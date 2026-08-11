<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\BookSession;
use App\Models\Room;
use App\Models\SessionPackage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Account as StripeAccount;
use Stripe\Stripe;

class UserDashboardController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Get the currency symbol for a creator, cached to avoid Stripe rate limits.
     */
    private function getCreatorCurrencySymbol($creator)
    {
        if (! $creator || ! $creator->stripe_account_id) {
            return '$';
        }

        $cacheKey = 'creator_currency_'.$creator->id;
        $currency = cache()->remember($cacheKey, 86400, function () use ($creator) {
            try {
                $account = StripeAccount::retrieve($creator->stripe_account_id);

                return strtolower($account->default_currency ?? 'usd');
            } catch (\Exception $e) {
                Log::error('Stripe Account Retrieval Error for User Dashboard: '.$e->getMessage());

                return 'usd';
            }
        });

        $currencySymbols = [
            'usd' => '$', 'eur' => '€', 'gbp' => '£', 'aud' => 'A$',
            'cad' => 'C$', 'jpy' => '¥', 'inr' => '₹',
        ];

        return $currencySymbols[strtolower($currency)] ?? '$';
    }

    /**
     * User Dashboard sessions list
     */
  public function index(Request $request)
{
    try {
        $user = auth('api')->user();

        $perPage = (int) $request->input('per_page', 10);
        $currentPage = (int) $request->input('current_page', 1);

        $bookSessions = BookSession::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->with(['creator.category', 'sessionPackage', 'sessionUsage'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'current_page', $currentPage);

        $formattedSessions = $bookSessions->map(function ($session) use ($user, $request) {

            // Room between users
            $room = Room::where(function ($query) use ($user, $session) {
                $query->where('user_one_id', $user->id)
                      ->where('user_two_id', $session->creator_id);
            })->orWhere(function ($query) use ($user, $session) {
                $query->where('user_one_id', $session->creator_id)
                      ->where('user_two_id', $user->id);
            })->first();

            $completedTime = null;

            $package = $session->sessionPackage;
            $usage = $session->sessionUsage;

            $offerName = null;
            if ($package) {
                if ($request->lang == 'en') {
                    $offerName = $package->name_en;
                } elseif ($request->lang == 'fr') {
                    $offerName = $package->name_fr;
                } elseif ($request->lang == 'es') {
                    $offerName = $package->name_es;
                } else {
                    $offerName = $package->name;
                }
            }

            if ($package) {
                if ($package->type === 'vip_access') {

                    $endDate = Carbon::parse($session->created_at)->addMonth();

                    if ($endDate->isPast()) {
                        $completedTime = $endDate->format('Y-m-d H:i:s');
                    }

                } 
                else {

                    if ($usage && $usage->is_completed) {
                        $completedTime = $usage->updated_at?->format('Y-m-d H:i:s');
                    }
                }
            }

            return [
                'id' => $session->id,
                'user_id' => $user->id,
                'receiver_id' => $session->creator_id,
                'room_id' => $room?->id,

                'creator_name' => $session->creator->name,
                'creator_avatar' => $session->creator->avatar 
                    ? asset($session->creator->avatar) 
                    : null,
                'creator_category' => $session->creator->category?->name,

                'package_name' => $offerName,
                'currency_symbol' => $this->getCreatorCurrencySymbol($session->creator),
                'package_price' => number_format($session->price, 2, '.', ''),
                'duration' => $package?->duration,

                'completed_time' => $completedTime,
            ];
        });

        return Helper::jsonResponse(true, 'User dashboard sessions retrieved successfully.', 200, [
            'sessions' => $formattedSessions,
            'pagination' => [
                'total_page' => $bookSessions->lastPage(),
                'per_page' => $bookSessions->perPage(),
                'total_item' => $bookSessions->total(),
                'current_page' => $bookSessions->currentPage(),
            ],
        ]);

    } catch (\Exception $e) {
        Log::error('User Dashboard Error: ' . $e->getMessage());

        return Helper::jsonErrorResponse(
            config('app.debug') ? $e->getMessage() : 'Internal server error',
            500
        );
    }
}

    /**
     * User Dashboard transactions/billing list
     */
    public function transactions(Request $request)
    {
        try {
            $user = auth('api')->user();

            $perPage = (int) $request->input('per_page', 10);
            $currentPage = (int) $request->input('current_page', 1);

            // Fetch BookSessions where the user paid for a session
            $bookSessions = BookSession::where('user_id', $user->id)
                ->whereIn('payment_status', ['paid', 'failed'])
                ->with(['creator', 'sessionPackage'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'current_page', $currentPage);

            $formattedTransactions = $bookSessions->map(function ($session) use ($request) {
                $currencySymbol = $this->getCreatorCurrencySymbol($session->creator);

                $offerName = null;
                $package = $session->sessionPackage;
                if ($package) {
                    if ($request->lang == 'en') {
                        $offerName = $package->name_en;
                    } elseif ($request->lang == 'fr') {
                        $offerName = $package->name_fr;
                    } elseif ($request->lang == 'es') {
                        $offerName = $package->name_es;
                    } else {
                        $offerName = $package->name;
                    }
                }

                return [
                    'id' => $session->id,
                    'transaction_id' => $session->transaction_id,
                    'creator' => [
                        'id' => $session->creator->id ?? null,
                        'name' => $session->creator->name ?? 'Deleted Creator',
                        'avatar' => $session->creator->avatar ?? null,
                    ],
                    'offer' => $offerName,
                    'type' => $session->sessionPackage->type ?? 'N/A',
                    'amount' => number_format($session->price, 2, '.', ''),
                    'currency_symbol' => $currencySymbol,
                    'payment_date' => Carbon::parse($session->created_at)->format('d M, Y h:i A'),
                    'payment_status' => $session->payment_status,
                    'payment_method' => $session->payment_method ?? 'Stripe',
                ];
            });

            $responsePayload = [
                'transactions' => $formattedTransactions,
                'pagination' => [
                    'total_page' => $bookSessions->lastPage(),
                    'per_page' => $bookSessions->perPage(),
                    'total_item' => $bookSessions->total(),
                    'current_page' => $bookSessions->currentPage(),
                ],
            ];

            return Helper::jsonResponse(true, 'User billing history retrieved successfully.', 200, $responsePayload);

        } catch (\Exception $e) {
            Log::error('User Billing History Error: '.$e->getMessage());

            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }
}
