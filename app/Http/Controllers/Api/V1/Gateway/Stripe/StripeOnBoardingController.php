<?php

namespace App\Http\Controllers\Api\V1\Gateway\Stripe;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BookSession;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Balance;
use Stripe\Exception\ApiErrorException;
use Stripe\File;
use Stripe\Payout;
use Stripe\Stripe;
use Throwable;

class StripeOnBoardingController extends Controller
{

    public $success_url;
    public $refresh_url;
    public function __construct()
    {
        $this->success_url = 'https://vuqia.net/creator-dashboard/payments/stripe-onboarding-success';
        $this->refresh_url = 'https://vuqia.net/creator-dashboard/payments/stripe-onboarding-refresh';
        Stripe::setApiKey(config('services.stripe.secret'));
    }

public function accountConnect(Request $request)
{
    
    $user = auth('api')->user();

    try {
        // Check if user already has an account and it is fully onboarded
        if ($user->stripe_account_id) {
            $account = Account::retrieve($user->stripe_account_id);
            if ($account->details_submitted) {
                $user->update(['is_account_added' => true]);
                return response()->json(['status' => 'success', 'message' => 'Account already connected and onboarded.'], 200);
            }
        } else {
            $account = Account::create([
                'type' => 'express',
                'email' => $user->email,
                'country' => strtoupper($request->country),
                'business_type' => 'individual',
                
                'business_profile' => [
                    'product_description' => 'Freelance services offered through the platform',
                ],
                'capabilities' => [
                    'transfers' => ['requested' => true],
                     'card_payments' => ['requested' => true],
                ],
                'settings' => [
                    'payouts' => [
                        'schedule' => [
                            'interval' => 'manual',
                        ],
                    ],
                ],
            ]);

          
        }

        $link = AccountLink::create([
            'account'     => $account->id,
            'refresh_url' => route('api.payment.stripe.account.connect.refresh', ['account_id' => $account->id]),
            'return_url'  => route('api.payment.stripe.account.connect.success', ['account_id' => $account->id]),
            'type'        => 'account_onboarding',
        ]);

        $data = [
            'url' => $link->url,
        ];

        return response()->json(['status' => 'success', 'data' => $data, 'message' => 'Redirecting to Stripe Express Dashboard..'], 200);
    } catch (ApiErrorException $e) {
        return response()->json(['status' => 'error', 'message' => 'Stripe API error: '.$e->getMessage()], 500);
    } catch (Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Error: '.$e->getMessage()], 500);
    }
}
    public function accountSuccess($account_id)
    {
        try {

            $account = Account::retrieve($account_id);
            $user = User::where('email', $account->email)->first();
            if (! $user) {
                return response()->json(['status' => 'error', 'message' => 'User not found in the database for this Stripe account.'], 404);
            }

            // Check if the user actually completed the onboarding
            if ($account->details_submitted) {
                $user->update([
                    'stripe_account_id' => $account_id,
                    'is_bank_added' => true,
                    'is_account_added' => true // assuming you also want to set this based on your question
                ]);
            } else {
                // If they didn't complete it, you might want to redirect them somewhere else or just not update
                // For now, we just redirect back to a failure or the same success URL which will show it's not complete
            }

            $token = uniqid();
        
            return redirect()->away($this->success_url.'?token='.$token);

        } catch (Exception $e) {

            return response()->json(['status' => 'error', 'message' => 'Error processing onboarding success: '.$e->getMessage()], 500);
        }
    }

    public function accountRefresh($account_id)
    {
        try {

            $link = AccountLink::create([
                'account' => $account_id,
                'refresh_url' => route('api.payment.stripe.account.connect.refresh', ['account_id' => $account_id]),
                'return_url' => route('api.payment.stripe.account.connect.success', ['account_id' => $account_id]),
                'type' => 'account_onboarding',
            ]);

            return redirect()->away($link->url);

        } catch (Exception $e) {

            return response()->json(['status' => 'error', 'message' => 'Error generating refresh link: '.$e->getMessage()], 500);

        }
    }

    public function accountUrl()
    {
        $user = auth('api')->user();

        if ($user->stripe_account_id) {
            try {
                $loginLink = Account::createLoginLink($user->stripe_account_id);

                $data = [
                    'url' => $loginLink->url,
                ];

                return response()->json(['status' => 'success', 'data' => $data, 'message' => 'Redirecting to Stripe Express Dashboard..'], 200);
            } catch (Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Error generating Stripe login link: '.$e->getMessage()], 500);
            }
        }
    }

    public function accountInfo()
    {
        $user = auth('api')->user();

        if ($user->stripe_account_id) {
            try {
                $account = Account::retrieve($user->stripe_account_id);

                $data = [
                    'account_id' => $account->id,
                    'email' => $account->email,
                    'payouts_enabled' => $account->payouts_enabled,
                    'is_bank_added' => isset($account->external_accounts) && count($account->external_accounts->data) > 0,

                ];

                return response()->json(['status' => 'success', 'data' => $data, 'message' => 'Account info retrieved successfully.', 'code' => 200], 200);
            } catch (Exception $e) {
                Log::info($e->getMessage());

                return response()->json(['status' => 'error', 'message' => 'Error retrieving account info: '.$e->getMessage(), 'code' => 500], 500);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => 'User does not have a connected Stripe account.', 'code' => 404], 200);
        }
    }

    public function withdraw(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01',
            ]);
            $validatedData = $validator->validated();
            $user = auth('api')->user();

            if (! $user || ! $user->stripe_account_id) {
                return Helper::jsonResponse(false, 'User does not have a connected Stripe account.', 404);
            }

            $account = Account::retrieve($user->stripe_account_id);
            if (! $account) {
                return Helper::jsonResponse(false, 'Stripe account not found.', 404);
            }

            $accountCurrency = strtolower($account->default_currency ?? 'usd');

            $availableBalance = 0;
            $balance = Balance::retrieve(['stripe_account' => $user->stripe_account_id]);
            if (! empty($balance->available)) {
                foreach ($balance->available as $item) {
                    if (strtolower($item->currency) === $accountCurrency) {
                        $availableBalance = $item->amount / 100;
                        break;
                    }
                }
            }

            if ($availableBalance <= 0) {
                return Helper::jsonResponse(false, 'You do not have enough balance to withdraw.', 400);
            }

            if ($validatedData['amount'] > $availableBalance) {
                return Helper::jsonResponse(false, 'You do not have enough balance to withdraw.', 400);
            }

            Payout::create([
                'amount' => $validatedData['amount'] * 100,
                'currency' => $accountCurrency,
            ], ['stripe_account' => $user->stripe_account_id]);

            return Helper::jsonResponse(true, 'Withdrawal request sent successfully.', 200);
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

    public function listBanks()
    {
        $user = auth('api')->user();

        if (! $user->stripe_account_id) {
            return response()->json(['status' => 'error', 'message' => 'User does not have a connected Stripe account.', 'code' => 404], 404);
        }

        try {
            $banks = Account::allExternalAccounts(
                $user->stripe_account_id,
                ['object' => 'bank_account']
            );

            $formattedBanks = array_map(function ($bank) {
                return [
                    'id' => $bank->id,
                    'bank_name' => $bank->bank_name ?? null,
                    'account_type' => $bank->account_holder_type ?? null,
                    'account_holder_name' => $bank->account_holder_name ?? null,
                    'routing_number' => $bank->routing_number ?? null,
                    'account_number' => '.... '.$bank->last4 ?? null,
                    'is_default' => $bank->default_for_currency ?? null,
                ];
            }, $banks->data);

            return response()->json(['status' => 'success', 'message' => 'Bank accounts retrieved successfully.', 'data' => $formattedBanks], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error retrieving bank accounts: '.$e->getMessage(), 'code' => 500], 500);
        }
    }

    public function addBank(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'account_holder_name' => 'required|string',
                'account_type' => 'required|string|in:individual,company',
                'routing_number' => 'required|string',
                'account_number' => 'required|string',
                'country' => 'nullable|string|size:2',
                'currency' => 'nullable|string|size:3',
            ]);
            $user = auth('api')->user();

            if (! $user->stripe_account_id) {
                return response()->json(['status' => 'error', 'message' => 'User does not have a connected Stripe account.', 'code' => 404], 404);
            }

            $account = Account::retrieve($user->stripe_account_id);
            $country = $request->country ?? $account->country;
            $currency = $request->currency ?? $account->default_currency ?? 'usd';

            $bank = Account::createExternalAccount(
                $user->stripe_account_id,
                [
                    'external_account' => [
                        'object' => 'bank_account',
                        'country' => $country,
                        'currency' => $currency,
                        'account_holder_name' => $request->account_holder_name,
                        'account_holder_type' => $request->account_type,
                        'routing_number' => $request->routing_number,
                        'account_number' => $request->account_number,
                    ],
                ]
            );

            $user->update(['is_bank_added' => true]);

            return response()->json(['status' => 'success', 'data' => $bank, 'message' => 'Bank account added successfully.', 'code' => 200], 200);
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

    public function deleteBank($bank_id)
    {
        $user = auth('api')->user();

        if (! $user->stripe_account_id) {
            return response()->json(['status' => 'error', 'message' => 'User does not have a connected Stripe account.', 'code' => 404], 404);
        }

        try {
            $deleted = Account::deleteExternalAccount(
                $user->stripe_account_id,
                $bank_id
            );

            $remainingBanks = Account::allExternalAccounts(
                $user->stripe_account_id,
                ['object' => 'bank_account']
            );

            if (count($remainingBanks->data) === 0) {
                $user->update(['is_bank_added' => false]);
            }

            return response()->json(['status' => 'success', 'data' => $deleted, 'message' => 'Bank account deleted successfully.', 'code' => 200], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error deleting bank account: '.$e->getMessage()], 500);
        }
    }

    public function setDefaultBank($bank_id)
    {
        $user = auth('api')->user();

        if (! $user->stripe_account_id) {
            return response()->json(['status' => 'error', 'message' => 'User does not have a connected Stripe account.', 'code' => 404], 404);
        }

        try {
            $bank = Account::updateExternalAccount(
                $user->stripe_account_id,
                $bank_id,
                ['default_for_currency' => true]
            );

            return response()->json(['status' => 'success', 'message' => 'Bank account set as default successfully.', 'code' => 200], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error setting default bank account: '.$e->getMessage(), 'code' => 500], 500);
        }
    }

    public function deleteAccount()
    {
        $user = auth('api')->user();

        if (! $user->stripe_account_id) {
            return response()->json(['status' => 'error', 'message' => 'User does not have a connected Stripe account.', 'code' => 404], 404);
        }

        try {
            $account = Account::retrieve($user->stripe_account_id);
            $deleted = $account->delete();

            // Delete all booked sessions associated with this creator
            BookSession::where('creator_id', $user->id)->delete();

            $user->update([
                'stripe_account_id' => null,
                'is_bank_added' => false,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $deleted,
                'message' => 'Stripe account deleted successfully.',
                'code' => 200,
            ], 200);
        } catch (ApiErrorException $e) {
            return response()->json(['status' => 'error', 'message' => 'Stripe API error: '.$e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error deleting Stripe account: '.$e->getMessage()], 500);
        }
    }

    public function getWithdrawalsHistory(Request $request)
    {
        $user = auth('api')->user();

        if (! $user->stripe_account_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'User does not have a connected Stripe account.',
                'code' => 404,
            ], 404);
        }

        try {
            $account = Account::retrieve($user->stripe_account_id);
            $accountCurrency = strtolower($account->default_currency ?? 'usd');

            $currencySymbols = [
                'usd' => '$', 'eur' => '€', 'gbp' => '£', 'aud' => 'A$',
                'cad' => 'C$', 'jpy' => '¥', 'inr' => '₹',
            ];

            $payouts = Payout::all(
                ['limit' => 100],
                ['stripe_account' => $user->stripe_account_id]
            );

            $formattedPayouts = [];

            foreach ($payouts->data as $payout) {

                $statusMap = [
                    'paid' => 'COMPLETED',
                    'pending' => 'PENDING',
                    'in_transit' => 'PENDING',
                    'failed' => 'FAILED',
                    'canceled' => 'FAILED',
                ];

                $payoutCurrency = strtolower($payout->currency);
                $formattedPayouts[] = [
                    'id' => $payout->id,
                    'amount' => number_format($payout->amount / 100, 2, '.', ''),
                    'currency' => strtoupper($payoutCurrency),
                    'currency_icon' => $currencySymbols[$payoutCurrency] ?? strtoupper($payoutCurrency),
                    'status' => $statusMap[$payout->status] ?? strtoupper($payout->status),
                    'method' => 'Stripe Payout',
                    'date' => Carbon::createFromTimestamp($payout->created)
                        ->format('D, j M Y'),
                ];
            }

            $perPage = (int) $request->input('per_page', 10);
            $currentPage = (int) $request->input('current_page', 1);

            $totalItems = count($formattedPayouts);
            $offset = ($currentPage - 1) * $perPage;
            $itemsForCurrentPage = array_slice($formattedPayouts, $offset, $perPage);

            $paginator = new LengthAwarePaginator(
                $itemsForCurrentPage,
                $totalItems,
                $perPage,
                $currentPage,
                ['path' => Paginator::resolveCurrentPath()]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Withdrawal history retrieved successfully.',
                'data' => [
                    'currency' => strtoupper($accountCurrency),
                    'history' => array_values($paginator->items()),
                    'pagination' => [
                        'total_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total_item' => $paginator->total(),
                        'current_page' => $paginator->currentPage(),
                    ],
                ],
            ]);

        } catch (Exception $e) {

            \Log::error('Stripe Withdrawal Error', [
                'account_id' => $user->stripe_account_id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error retrieving withdrawal history: '.$e->getMessage(),
            ], 500);
        }
    }
}
