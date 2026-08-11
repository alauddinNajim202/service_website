<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\MessageSendEvent;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\BookSession;
use App\Models\Chat;
use App\Models\CreatorSessionPrice;
use App\Models\Room;
use App\Models\SessionPackage;
use App\Models\User;
use App\Notifications\SessionStartNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\Account as StripeAccount;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class BookSessionController extends Controller
{
    public $redirectFail;

    public $redirectSuccess;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $this->redirectFail = config('settings.fail');
        $this->redirectSuccess = config('settings.success_url');
    }

    /**
     * Get the default currency of a creator's connected Stripe account.
     */
    private function getCreatorCurrency(?User $creator): ?string
    {
        if (! $creator || ! $creator->stripe_account_id) {
            return null;
        }

        try {
            $account = StripeAccount::retrieve($creator->stripe_account_id);

            return $account->default_currency ?? null;
        } catch (\Exception $e) {
            Log::error('Stripe Account Currency Error: '.$e->getMessage());

            return null;
        }
    }

    public function checkout(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'creator_id' => 'required|exists:users,id',
            'session_package_id' => 'required|exists:session_packages,id',
            'booking_date' => 'nullable|date',
            'booking_time' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Helper::jsonResponse(
                false,
                'Validation failed',
                422,
                $validator->errors()
            );
        }

        try {
            $data = $validator->validated();
            $user = auth('api')->user();

            $package = SessionPackage::findOrFail($data['session_package_id']);
            $creator = User::findOrFail($data['creator_id']);

            if (empty($creator->stripe_account_id)) {
                return Helper::jsonResponse(
                    false,
                    'Creator Stripe account is not connected.',
                    422
                );
            }

            $currency = strtolower(
                $this->getCreatorCurrency($creator) ?? 'usd'
            );

            $customPrice = CreatorSessionPrice::where(
                'creator_id',
                $creator->id
            )
                ->where('session_package_id', $package->id)
                ->first();

            $finalPrice = $customPrice
                ? $customPrice->price
                : $package->price;

            $creatorCurrency = strtolower($this->getCreatorCurrency($creator) ?? 'usd');
            $baseCurrency = 'usd';

            $conversionRate = 1;
            if ($creatorCurrency !== $baseCurrency) {
                $conversionRate = Cache::remember('exchange_rate_'.$baseCurrency.'_'.$creatorCurrency, 3600, function () use ($baseCurrency, $creatorCurrency) {
                    try {
                        $response = Http::get('https://open.er-api.com/v6/latest/'.strtoupper($baseCurrency));
                        if ($response->successful()) {
                            $rates = $response->json('rates');

                            return $rates[strtoupper($creatorCurrency)] ?? 1;
                        }
                    } catch (\Exception $e) {
                        Log::error('Currency conversion error: '.$e->getMessage());
                    }

                    return 1;
                });
            }

            $convertedPrice = round($finalPrice * $conversionRate, 2);

            // Calculate Customer's Local Currency based on IP
            $ip = $request->ip();
            $customerCurrency = Cache::remember('user_currency_'.$ip, 86400, function () use ($ip) {
                try {
                    $url = ($ip === '127.0.0.1' || $ip === '::1') ? 'http://ip-api.com/json/?fields=currency' : "http://ip-api.com/json/{$ip}?fields=currency";
                    $response = Http::get($url);
                    if ($response->successful()) {
                        return $response->json('currency') ?? 'USD';
                    }
                } catch (\Exception $e) {
                    // ignore
                }

                return 'USD';
            });

            $customerConversionRate = 1;
            if ($customerCurrency && strtoupper($customerCurrency) !== strtoupper($baseCurrency)) {
                $customerConversionRate = Cache::remember('exchange_rate_'.$baseCurrency.'_'.strtoupper($customerCurrency), 3600, function () use ($baseCurrency, $customerCurrency) {
                    try {
                        $response = Http::get('https://open.er-api.com/v6/latest/'.strtoupper($baseCurrency));
                        if ($response->successful()) {
                            $rates = $response->json('rates');

                            return $rates[strtoupper($customerCurrency)] ?? 1;
                        }
                    } catch (\Exception $e) {
                    }

                    return 1;
                });
            }
            $customerLocalPrice = round($finalPrice * $customerConversionRate, 2);

            $amountInCents = (int) round($convertedPrice * 100);
            $platformFee = (int) round($amountInCents * 0.25);
            $creatorEarning = round(($amountInCents - $platformFee) / 100, 2);

            $bookSession = BookSession::create([
                'user_id' => $user->id,
                'creator_id' => $creator->id,
                'session_package_id' => $package->id,
                'booking_date' => $data['booking_date'] ?? null,
                'booking_time' => $data['booking_time'] ?? null,
                'price' => $convertedPrice,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'stripe',
            ]);

            $successUrl = route('v1api.book-session.success')
                .'?token={CHECKOUT_SESSION_ID}';

            $cancelUrl = route('v1api.book-session.cancel')
                .'?token={CHECKOUT_SESSION_ID}';

            $stripeSession = StripeSession::create([
                'payment_method_types' => ['card'],
                'customer_email' => $user->email,

                'line_items' => [[
                    'price_data' => [
                        'currency' => $creatorCurrency,
                        'product_data' => [
                            'name' => 'Session Booking: '.$package->name,
                            'description' => "Estimated local price: ~ {$customerLocalPrice} {$customerCurrency}",
                        ],
                        'unit_amount' => $amountInCents,
                    ],
                    'quantity' => 1,
                ]],

                'mode' => 'payment',

                'payment_intent_data' => [
                    'application_fee_amount' => $platformFee,
                    'transfer_data' => [
                        'destination' => $creator->stripe_account_id,
                    ],
                ],

                'metadata' => [
                    'book_session_id' => $bookSession->id,
                    'user_id' => $user->id,
                    'creator_id' => $creator->id,
                    'session_package_id' => $package->id,
                    'base_price' => $finalPrice,
                    'conversion_rate' => $conversionRate,
                ],

                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

            $bookSession->update([
                'transaction_id' => $stripeSession->id,
            ]);

            return Helper::jsonResponse(
                true,
                'Checkout session created successfully',
                200,
                [
                    'checkout_url' => $stripeSession->url,
                ]
            );

        } catch (ApiErrorException $e) {

            Log::error('Stripe API Error: '.$e->getMessage());

            return Helper::jsonResponse(
                false,
                'Payment provider error',
                500,
                [
                    'error' => $e->getMessage(),
                ]
            );

        } catch (\Exception $e) {

            Log::error('Checkout Error: '.$e->getMessage());

            return Helper::jsonResponse(
                false,
                'Internal server error',
                500,
                [
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function success(Request $request)
    {
        $validatedData = $request->validate([
            'token' => ['required', 'string'],
        ]);

        try {
            $stripeSession = StripeSession::retrieve($validatedData['token']);

            $bookSessionId = $stripeSession->metadata['book_session_id'] ?? null;
            if (! $bookSessionId) {
                return redirect()->to($this->redirectFail);
            }

            $bookSession = BookSession::find($bookSessionId);
            if (! $bookSession) {
                return redirect()->to($this->redirectFail);
            }

            if ($stripeSession->payment_status === 'paid') {
                $creator = User::find($bookSession->creator_id);
                $packageDetail = SessionPackage::find($bookSession->session_package_id);
                $currency = $this->getCreatorCurrency($creator) ?? ($stripeSession->currency ?? '');

                // Custom price logic: use creator-specific price if set, otherwise fall back to package default
                $customPrice = CreatorSessionPrice::where('creator_id', $creator->id)
                    ->where('session_package_id', $packageDetail->id)
                    ->first();

                $finalPrice = $customPrice ? $customPrice->price : $packageDetail->price;

                $bookSession->update([
                    'payment_status' => 'paid',
                    'status' => 'approved',
                    'transaction_id' => $stripeSession->payment_intent ?? $stripeSession->id,
                ]);

                // Send Notifications
                $customer = User::find($bookSession->user_id);
                if ($customer && $customer->session_start_notification == 1) {
                    $customer->notify(new SessionStartNotification($bookSession, false));
                }
                
                if ($creator && $creator->session_start_notification == 1) {
                    $creator->notify(new SessionStartNotification($bookSession, true));
                }

                $queryParams = [
                    'creator_name' => $creator->name ?? '',
                    'package_name' => $packageDetail->name ?? '',
                    'package_price' => $finalPrice,
                    'currency' => $currency,
                    'token' => $validatedData['token'],
                ];

                $redirectUrl = $this->redirectSuccess.'?'.http_build_query($queryParams);

                $senderId = $bookSession->user_id;
                $receiverId = $bookSession->creator_id;
                $room = Room::where(function ($query) use ($receiverId, $senderId) {
                    $query->where('user_one_id', $receiverId)->where('user_two_id', $senderId);
                })->orWhere(function ($query) use ($receiverId, $senderId) {
                    $query->where('user_one_id', $senderId)->where('user_two_id', $receiverId);
                })->first();

                if (! $room) {
                    $room = Room::create([
                        'user_one_id' => $senderId,
                        'user_two_id' => $receiverId,
                    ]);
                }

                $chat = Chat::create([
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId,
                    'text' => $packageDetail->name.' Session Package has been purchased successfully!',
                    'file' => null,
                    'room_id' => $room->id,
                    'status' => 'sent',
                ]);

                $chat->load([
                    'sender:id,name,email,avatar,last_activity_at',
                    'receiver:id,name,email,avatar,last_activity_at',
                    'room:id,user_one_id,user_two_id',
                ]);

                try {
                    broadcast(new MessageSendEvent($chat))->toOthers();
                } catch (\Exception $e) {
                    Log::error('Broadcast error in BookSession success: '.$e->getMessage());
                }

                return redirect()->to($redirectUrl);
            }

            if (in_array($stripeSession->payment_status, ['unpaid', 'no_payment_required'])) {
                $bookSession->update(['payment_status' => 'failed']);

                return redirect()->to($this->redirectFail.'?session_id='.$bookSession->id);
            }

            return redirect()->to($this->redirectFail.'?session_id='.$bookSession->id);

        } catch (ApiErrorException $e) {
            Log::error('Stripe Success Error: '.$e->getMessage());

            return redirect()->to($this->redirectFail);
        } catch (\Exception $e) {
            Log::error('Book Session Success Error: '.$e->getMessage());

            return redirect()->to($this->redirectFail);
        }
    }

    public function cancel(Request $request)
    {
        $token = $request->query('token');
        $creator = null;
        $packageDetail = null;
        $currency = null;

        if ($token) {
            try {
                $stripeSession = StripeSession::retrieve($token);
                $bookSessionId = $stripeSession->metadata['book_session_id'] ?? null;

                if ($bookSessionId) {
                    BookSession::where('id', $bookSessionId)->update(['payment_status' => 'failed']);
                }

                $creatorId = $stripeSession->metadata['creator_id'] ?? null;
                $packageId = $stripeSession->metadata['session_package_id'] ?? null;

                $creator = $creatorId ? User::find($creatorId) : null;
                $packageDetail = $packageId ? SessionPackage::find($packageId) : null;
                $currency = $this->getCreatorCurrency($creator) ?? ($stripeSession->currency ?? '');

            } catch (\Exception $e) {
                Log::error('Stripe Cancel Error: '.$e->getMessage());
            }
        }

        // Custom price logic: use creator-specific price if set, otherwise fall back to package default
        $finalPrice = $packageDetail->price ?? '';
        if ($creator && $packageDetail) {
            $customPrice = CreatorSessionPrice::where('creator_id', $creator->id)
                ->where('session_package_id', $packageDetail->id)
                ->first();

            if ($customPrice) {
                $finalPrice = $customPrice->price;
            }
        }

        $queryParams = [
            'creator_name' => $creator->name ?? '',
            'package_name' => $packageDetail->name ?? '',
            'package_price' => $finalPrice,
            'currency' => $currency ?? '',
            'token' => $token ?? '',
        ];

        $redirectUrl = $this->redirectFail.'?'.http_build_query($queryParams);

        return redirect()->to($redirectUrl);
    }
}
