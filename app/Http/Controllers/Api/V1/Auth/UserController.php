<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\SessionPackage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Stripe\Account;
use Stripe\Balance;
use Stripe\Stripe;
use Throwable;

class UserController extends Controller
{
    public $select;

    public function __construct()
    {
        parent::__construct();
        $this->select = ['id', 'name', 'email', 'avatar', 'otp_verified_at', 'last_activity_at'];
        Stripe::setApiKey(config('services.stripe.secret'));

    }

    public function me()
    {
        try {
            $user = auth('api')->user();
            $account = null;
            $availableBalance = 0;
            $pendingBalance = 0;
            $accountCurrency = 'usd';

            if ($user->stripe_account_id) {
                try {
                    $account = Account::retrieve($user->stripe_account_id);
                    $accountCurrency = strtolower($account->default_currency ?? 'usd');

                    $balance = Balance::retrieve([
                        'stripe_account' => $user->stripe_account_id,
                    ]);

                    if (! empty($balance->available)) {
                        foreach ($balance->available as $item) {
                            if (strtolower($item->currency) === $accountCurrency) {
                                $availableBalance = $item->amount / 100;
                                break;
                            }
                        }
                    }

                    if (! empty($balance->pending)) {
                        foreach ($balance->pending as $item) {
                            if (strtolower($item->currency) === $accountCurrency) {
                                $pendingBalance = $item->amount / 100;
                                break;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $user->update([
                        'stripe_account_id' => null,
                        'is_bank_added' => false,
                    ]);
                }
            }

            if ($user->hasRole('creator')) {
                $vipPackage = SessionPackage::where('type', 'vip_access')->first();
                $vipPrice = $vipPackage
                    ? ($user->userPackgae()->where('session_package_id', $vipPackage->id)->value('price') ?? (float) $vipPackage->price)
                    : null;

                $data = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'category' => [
                        'id' => $user->category?->id,
                        'name' => $user->category?->name,
                    ],
                    'short_bio' => $user->short_bio,
                    'role' => $user->role,
                    'price' => $vipPrice,
                    'stripe_account_id' => $user->stripe_account_id ? true : false,
                    'is_bank_added' => $user->is_bank_added ? true : false,
                    'session_start_notification' => (bool) $user->session_start_notification,
                    'new_message_notification' => (bool) $user->new_message_notification,
                    'payment_update_notification' => (bool) $user->payment_update_notification,
                    'stripe_account_details' => $user->stripe_account_id && $account ? [
                        'stripe_account_id' => $user->stripe_account_id,
                        'email' => $account->email ?? null,
                        'name' => trim(($account->individual?->first_name ?? '').' '.($account->individual?->last_name ?? '')),
                        'phone' => $account->individual?->phone ?? null,
                        'address' => $account->individual?->address ?? null,
                        'available_balance' => number_format($availableBalance, 2, '.', ''),
                        'pending_balance' => number_format($pendingBalance, 2, '.', ''),
                        'currency' => strtoupper($accountCurrency),
                        'currency_symbol' => match (strtolower($accountCurrency)) {
                            'eur' => '€',
                            'gbp' => '£',
                            'aud' => 'A$',
                            'cad' => 'C$',
                            'jpy' => '¥',
                            'inr' => '₹',
                            default => '$',
                        },
                    ] : null,
                ];

            } else {
                $data = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'role' => $user->role,
                    'session_start_notification' => (bool) $user->session_start_notification,
                    'new_message_notification' => (bool) $user->new_message_notification,
                    'payment_update_notification' => (bool) $user->payment_update_notification,
                ];

            }

            return Helper::jsonResponse(true, 'User retrieved successfully.', 200, $data);

        } catch (ValidationException $e) {
            DB::rollBack();

            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {
            DB::rollBack();

            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = auth('api')->user();

            $rules = [
                'name' => 'required|string|max:100',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'phone' => 'nullable|string|numeric|max_digits:20',
                'address' => 'nullable|string|max:255',
            ];

            $validatedData = $request->validate($rules);

            if ($request->hasFile('avatar')) {
                if (! empty($user->avatar)) {
                    Helper::fileDelete(public_path($user->getRawOriginal('avatar')));
                }
                $validatedData['avatar'] = Helper::fileUpload(
                    $request->file('avatar'),
                    'user/avatar',
                    getFileName($request->file('avatar'))
                );
            } else {
                $validatedData['avatar'] = $user->avatar;
            }

            $user->update($validatedData);

            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'role' => $user->role,
            ];

            return Helper::jsonResponse(true, 'Profile updated successfully', 200, $data);

        } catch (ValidationException $e) {
            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    public function updateCreatorProfile(Request $request)
    {
        try {
            $user = auth('api')->user();

            $rules = [
                'name' => 'required|string|max:100',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'phone' => 'nullable|string|numeric|max_digits:20',
                'address' => 'nullable|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'short_bio' => 'required|string',
            ];

            $validatedData = $request->validate($rules);

            if ($request->hasFile('avatar')) {
                if (! empty($user->avatar)) {
                    Helper::fileDelete(public_path($user->getRawOriginal('avatar')));
                }
                $validatedData['avatar'] = Helper::fileUpload(
                    $request->file('avatar'),
                    'user/avatar',
                    getFileName($request->file('avatar'))
                );
            } else {
                $validatedData['avatar'] = $user->avatar;
            }

            $user->update($validatedData);

            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'category_name' => $user->category?->name,
                'short_bio' => $user->short_bio,
                'role' => $user->role,
            ];

            return Helper::jsonResponse(true, 'Creator profile updated successfully', 200, $data);

        } catch (ValidationException $e) {
            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    public function updateAvatar(Request $request)
    {
        $validatedData = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $user = auth('api')->user();

        if (! empty($user->avatar)) {
            Helper::fileDelete(public_path($user->getRawOriginal('avatar')));
        }

        $validatedData['avatar'] = Helper::fileUpload(
            $request->file('avatar'),
            'user/avatar',
            getFileName($request->file('avatar'))
        );

        $user->update($validatedData);

        $data = User::select($this->select)->with('roles')->find($user->id);

        return Helper::jsonResponse(true, 'Avatar updated successfully', 200, $data);
    }

    public function delete()
    {
        $user = User::findOrFail(auth('api')->id());

        if (! empty($user->avatar) && file_exists(public_path($user->avatar))) {
            Helper::fileDelete(public_path($user->avatar));
        }

        Auth::logout('api');
        $user->delete();

        return Helper::jsonResponse(true, 'Profile deleted successfully', 200);
    }

    public function destroy()
    {
        $user = User::findOrFail(auth('api')->id());

        if (! empty($user->avatar) && file_exists(public_path($user->avatar))) {
            Helper::fileDelete(public_path($user->avatar));
        }

        Auth::logout('api');

        $user->forceDelete();

        return Helper::jsonResponse(true, 'Profile deleted successfully', 200);
    }

    public function users()
    {
        $users = User::select(['id', 'name', 'email'])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            });

        return Helper::jsonResponse(true, 'Users fetched successfully', 200, $users);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = auth('api')->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return Helper::jsonResponse(false, 'Current password does not match', 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return Helper::jsonResponse(true, 'Password updated successfully', 200);
    }

    public function getNotificationSettings()
    {
        try {
            $user = auth('api')->user();

            $data = [
                'session_start_notification' => (bool) $user->session_start_notification,
                'new_message_notification' => (bool) $user->new_message_notification,
                'payment_update_notification' => (bool) $user->payment_update_notification,
            ];

            return Helper::jsonResponse(true, 'Notification settings retrieved successfully.', 200, $data);
        } catch (Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    public function updateNotificationSettings(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|in:payment_update_notification,new_message_notification,session_start_notification',
            ]);

            $user = auth('api')->user();

            if ($request->type == 'payment_update_notification') {
                $user->payment_update_notification = ! $user->payment_update_notification;
            }

            if ($request->type == 'new_message_notification') {
                $user->new_message_notification = ! $user->new_message_notification;
            }

            if ($request->type == 'session_start_notification') {
                $user->session_start_notification = ! $user->session_start_notification;
            }

            $user->save();

            return Helper::jsonResponse(true, 'notification '.($user->{$request->type} ? 'activated' : 'deactivated').' updated successfully.', 200);
        } catch (ValidationException $e) {
            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }
}
