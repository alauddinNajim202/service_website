<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BookSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Account;
use Stripe\Balance;
use Stripe\Payout;
use Stripe\Stripe;
use App\Models\Transaction;

class CreatorDashboardController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function index(Request $request)
    {
        $user = auth('api')->user();

        // 1. Check if user has stripe connected
        if (! $user->stripe_account_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please connect a Stripe account to view your dashboard.',
                'code' => 404,
            ], 404);
        }

        try {
            // Retrieve Stripe Balance
            $balance = Balance::retrieve(['stripe_account' => $user->stripe_account_id]);
            $account = Account::retrieve($user->stripe_account_id);
            $accountCurrency = strtolower($account->default_currency ?? 'usd');

            $availableBalance = 0;
            $pendingBalance = 0;

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

            // Retrieve Payouts to sum already withdrawn
            $payouts = Payout::all(['limit' => 100], ['stripe_account' => $user->stripe_account_id]);
            $alreadyWithdrawn = 0;

            foreach ($payouts->data as $payout) {
                if ($payout->status === 'paid') {
                    $alreadyWithdrawn += ($payout->amount / 100);
                }
            }

            // Loop if they have more than 100 payouts
            while ($payouts->has_more) {
                $lastPayout = end($payouts->data);
                $payouts = Payout::all([
                    'limit' => 100,
                    'starting_after' => $lastPayout->id,
                ], ['stripe_account' => $user->stripe_account_id]);

                foreach ($payouts->data as $payout) {
                    if ($payout->status === 'paid') {
                        $alreadyWithdrawn += ($payout->amount / 100);
                    }
                }
            }

            // Total Stripe Earnings (Available + Pending + Withdrawn)
            $totalEarnings = $availableBalance + $pendingBalance + $alreadyWithdrawn;

            // This Month Earnings (From Database, creator receives 75%)
            $thisMonthEarnings = BookSession::where('creator_id', $user->id)
                ->where('payment_status', 'paid')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('price') * 0.75;

            // VIP Revenue (From Database, creator receives 75%)
            $vipRevenue = BookSession::where('creator_id', $user->id)
                ->where('payment_status', 'paid')
                ->whereHas('sessionPackage', function ($query) {
                    $query->where('type', 'vip_access');
                })->sum('price') * 0.75;
            $personalAdviceRevenue = BookSession::where('creator_id', $user->id)
                ->where('payment_status', 'paid')
                ->whereHas('sessionPackage', function ($query) {
                    $query->where('type', 'personal_advice');
                })->sum('price') * 0.75;
            $quickAdviceRevenue = BookSession::where('creator_id', $user->id)
                ->where('payment_status', 'paid')
                ->whereHas('sessionPackage', function ($query) {
                    $query->where('type', 'quick_advice');
                })->sum('price') * 0.75;


            $totalTipRevenue = Transaction::where('user_id', $user->id)
                ->where('type', 'increment')
                ->where('metadata->type', 'tip_received')
                ->sum('amount');

            return response()->json([
                'status' => 'success',
                'message' => 'Dashboard statistics retrieved successfully.',
                'code' => 200,
                'data' => [
                    'total_earnings' => round($totalEarnings, 2),
                    'this_month_earnings' => round($thisMonthEarnings, 2),
                    'available_for_withdrawal' => round($availableBalance, 2),
                    'already_withdrawn' => round($alreadyWithdrawn, 2),
                    'vip_revenue' => round($vipRevenue, 2),
                    'total_tip_revenue' => round($totalTipRevenue, 2),
                    'personal_advice_revenue' => round($personalAdviceRevenue, 2),
                    'quick_advice_revenue' => round($quickAdviceRevenue, 2),
                    'currency' => strtoupper($accountCurrency),
                    'symbol' => $this->getSymbol($accountCurrency),
                    'chart_data' => [
                        'this-week' => $this->getThisWeekData($user->id),
                        'this-month' => $this->getThisMonthData($user->id),
                        'last-month' => $this->getLastMonthData($user->id),
                        'this-year' => $this->getThisYearData($user->id),
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Stripe Dashboard Error', [
                'account_id' => $user->stripe_account_id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error retrieving dashboard statistics: '.$e->getMessage(),
                'code' => 500,
            ], 500);
        }
    }

    /**
     * Get recent earnings for the creator from Stripe.
     * Returns a list of recent payments received by the creator.
     * Response format matches getWithdrawalsHistory.
     */
    public function recentEarnings(Request $request)
    {
        $user = auth('api')->user();

        if (! $user->stripe_account_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please connect a Stripe account to view your earnings.',
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

            $perPage = (int) $request->input('per_page', 10);
            $currentPage = (int) $request->input('current_page', 1);

            // Query paid BookSessions for this creator with pagination
            $bookSessions = BookSession::where('creator_id', $user->id)
                ->where('payment_status', 'paid')
                ->with(['user:id,name,avatar', 'sessionPackage:id,name,type'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'current_page', $currentPage);

            $formattedEarnings = [];

            foreach ($bookSessions->items() as $session) {
                $statusMap = [
                    'paid' => 'COMPLETED',
                    'pending' => 'PENDING',
                    'failed' => 'FAILED',
                ];

                $formattedEarnings[] = [
                    'id' => $session->id,
                    'payment_id' => $session->transaction_id,
                    'user' => [
                        'name' => $session->user->name ?? 'No Name',
                        'avatar' => $session->user->avatar ?? null,
                    ],
                    'plan' => $session->sessionPackage->type ?? 'NO PLAN',
                    'amount' => number_format($session->price * 0.75, 2, '.', ''),
                    'currency' => strtoupper($accountCurrency),
                    'currency_icon' => $currencySymbols[$accountCurrency] ?? strtoupper($accountCurrency),
                    'status' => $statusMap[$session->payment_status] ?? strtoupper($session->payment_status),
                    'method' => 'Stripe Payment',
                    'date' => Carbon::parse($session->created_at)->format('D, d M Y'),
                ];
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Recent earnings retrieved successfully.',
                'data' => [
                    'currency' => strtoupper($accountCurrency),
                    'history' => $formattedEarnings,
                    'pagination' => [
                        'total_page' => $bookSessions->lastPage(),
                        'per_page' => $bookSessions->perPage(),
                        'total_item' => $bookSessions->total(),
                        'current_page' => $bookSessions->currentPage(),
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Stripe Recent Earnings Error', [
                'account_id' => $user->stripe_account_id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error retrieving recent earnings: '.$e->getMessage(),
                'code' => 500,
            ], 500);
        }
    }

    private function getThisWeekData($creatorId)
    {
        $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $data = [];

        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);

        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $earning = BookSession::where('creator_id', $creatorId)
                ->where('payment_status', 'paid')
                ->whereDate('created_at', $day->toDateString())
                ->sum('price') * 0.75;
            $data[] = round($earning, 2);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getThisMonthData($creatorId)
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $totalDays = $endOfMonth->day;
        $totalWeeks = (int) ceil($totalDays / 7);

        $labels = [];
        $data = [];

        for ($week = 1; $week <= $totalWeeks; $week++) {
            $labels[] = 'Week '.$week;
            $weekStart = $startOfMonth->copy()->addDays(($week - 1) * 7);
            $weekEnd = $weekStart->copy()->addDays(6);

            if ($weekEnd->gt($endOfMonth)) {
                $weekEnd = $endOfMonth->copy();
            }

            $earning = BookSession::where('creator_id', $creatorId)
                ->where('payment_status', 'paid')
                ->whereBetween('created_at', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                ->sum('price') * 0.75;
            $data[] = round($earning, 2);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getLastMonthData($creatorId)
    {
        $lastMonth = Carbon::now()->subMonth();
        $startOfMonth = $lastMonth->copy()->startOfMonth();
        $endOfMonth = $lastMonth->copy()->endOfMonth();
        $totalDays = $endOfMonth->day;
        $totalWeeks = (int) ceil($totalDays / 7);

        $labels = [];
        $data = [];

        for ($week = 1; $week <= $totalWeeks; $week++) {
            $labels[] = 'Week '.$week;
            $weekStart = $startOfMonth->copy()->addDays(($week - 1) * 7);
            $weekEnd = $weekStart->copy()->addDays(6);

            if ($weekEnd->gt($endOfMonth)) {
                $weekEnd = $endOfMonth->copy();
            }

            $earning = BookSession::where('creator_id', $creatorId)
                ->where('payment_status', 'paid')
                ->whereBetween('created_at', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                ->sum('price') * 0.75;
            $data[] = round($earning, 2);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getThisYearData($creatorId)
    {
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $data = [];
        $year = Carbon::now()->year;

        for ($month = 1; $month <= 12; $month++) {
            $earning = BookSession::where('creator_id', $creatorId)
                ->where('payment_status', 'paid')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('price') * 0.75;
            $data[] = round($earning, 2);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getSymbol($currency)
    {
        $symbols = [
            'usd' => '$',
            'eur' => '€',
            'gbp' => '£',
            'jpy' => '¥',
            'aud' => 'A$',
            'cad' => 'C$',
            'nzd' => 'NZ$',
            'chf' => 'Fr',
            'sek' => 'kr',
            'dkk' => 'kr',
            'nok' => 'kr',
            'hdk' => 'HK$',
            'sgd' => 'S$',
            'npr' => 'Rs.',
        ];

        return $symbols[$currency] ?? $currency;
    }
}
