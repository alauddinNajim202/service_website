<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\MessageSendEvent;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Room;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\MessageNotifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\Account as StripeAccount;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class TipController extends Controller
{
    // public $redirectFail;
    public $redirectSuccess;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        // $this->redirectFail = config('settings.fail') ?? url('/payment/fail');
        $this->redirectSuccess = config('settings.success_url') ?? url('/payment/success');
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

    /**
     * Send Tip Checkout (Stripe)
     */
    public function checkout(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'creator_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'message' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return Helper::jsonResponse(false, 'Validation failed', 422, $validator->errors());
        }

        try {
            $user = auth('api')->user();
            $creator = User::find($request->creator_id);
            $amount = $request->amount;
            $tipMessage = $request->message ?? '';

            if (empty($creator->stripe_account_id)) {
                return Helper::jsonResponse(false, 'Creator Stripe account is not connected.', 422);
            }

            if ($user->id == $creator->id) {
                return Helper::jsonResponse(false, 'Cannot tip yourself', 403);
            }

            $currency = strtolower($this->getCreatorCurrency($creator) ?? 'usd');

            $sessionData = [
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => 'Tip for ' . $creator->name,
                            'description' => $tipMessage,
                        ],
                        'unit_amount' => round($amount * 100), // in cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'payment_intent_data' => [
                    'transfer_data' => [
                        'destination' => $creator->stripe_account_id,
                    ],
                ],
                'success_url' => route('v1api.tip.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('v1api.tip.cancel') . '?session_id={CHECKOUT_SESSION_ID}',
                'metadata' => [
                    'sender_id' => $user->id,
                    'creator_id' => $creator->id,
                    'type' => 'chat_tip',
                    'amount' => $amount,
                    'message' => $tipMessage
                ],
                'customer_email' => $user->email,
            ];

            $session = StripeSession::create($sessionData);

            return Helper::jsonResponse(true, 'Checkout session created', 200, ['url' => $session->url]);
        } catch (\Exception $e) {
            Log::error('Send Tip Checkout Error: ' . $e->getMessage());
            return Helper::jsonResponse(false, 'Failed to create checkout session: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Send Tip Success Callback
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        // dd($sessionId);

        // if (!$sessionId) {
        //     return redirect()->to($this->redirectFail);
        // }

        DB::beginTransaction();
        try {
            $stripeSession = StripeSession::retrieve($sessionId);

            // if ($stripeSession->payment_status !== 'paid') {
            //     return redirect()->to($this->redirectFail);
            // }

            $senderId = $stripeSession->metadata['sender_id'] ?? null;
            $creatorId = $stripeSession->metadata['creator_id'] ?? null;
            $amount = $stripeSession->metadata['amount'] ?? 0;
            $tipMessage = $stripeSession->metadata['message'] ?? '';
            $trxId = $stripeSession->payment_intent;

            if ($senderId && $creatorId && $amount > 0) {
                // Check if transaction already exists
                $exists = Transaction::where('trx_id', $trxId)->exists();

                if (!$exists) {
                    $sender = User::find($senderId);
                    $creator = User::find($creatorId);

                    // 1. Transaction for sender (decrement)
                    $senderTrx = Transaction::create([
                        'user_id' => $senderId,
                        'title' => 'Sent Tip to ' . ($creator->name ?? 'Creator'),
                        'trx_id' => $trxId,
                        'amount' => $amount,
                        'currency' => $stripeSession->currency ?? 'usd',
                        'type' => 'decrement',
                        'gateway' => 'stripe',
                        'status' => 'success',
                        'metadata' => json_encode(['receiver_id' => $creatorId, 'type' => 'tip_sent', 'session_id' => $sessionId]),
                    ]);

                    // 2. Transaction for creator (increment)
                  $trnas =    Transaction::create([
                        'user_id' => $creatorId,
                        'title' => 'Received Tip from ' . ($sender->name ?? 'User'),
                        'trx_id' => $trxId,
                        'amount' => $amount,
                        'currency' => $stripeSession->currency ?? 'usd',
                        'type' => 'increment',
                        'gateway' => 'stripe',
                        'status' => 'success',
                        'metadata' => json_encode(['sender_id' => $senderId, 'type' => 'tip_received', 'session_id' => $sessionId]),
                    ]);


                    // 3. Create or find room
                    $room = Room::where(function ($query) use ($creatorId, $senderId) {
                        $query->where('user_one_id', $creatorId)->where('user_two_id', $senderId);
                    })->orWhere(function ($query) use ($creatorId, $senderId) {
                        $query->where('user_one_id', $senderId)->where('user_two_id', $creatorId);
                    })->first();

                    if (!$room) {
                        $room = Room::create([
                            'user_one_id' => $senderId,
                            'user_two_id' => $creatorId,
                        ]);
                    }

                    // 4. Create Chat Message
                    $chat = Chat::create([
                        'sender_id' => $senderId,
                        'receiver_id' => $creatorId,
                        'room_id' => $room->id,
                        'text' => 'Sent a tip of $' . $amount,
                        'message_type' => 'tip',
                        'metadata' => [
                            'amount' => $amount,
                            'message' => $tipMessage,
                            'transaction_id' => $senderTrx->id
                        ]
                    ]);

                    $chat->load([
                        'sender:id,name,email,avatar,last_activity_at',
                        'receiver:id,name,email,avatar,last_activity_at',
                        'room:id,user_one_id,user_two_id'
                    ]);

                    $data = [
                        'receiver' => User::select('id', 'name', 'email', 'avatar', 'last_activity_at')->where('id', $creatorId)->first(),
                        'sender' => User::select('id', 'name', 'email', 'avatar', 'last_activity_at')->where('id', $senderId)->first(),
                        'room' => $room,
                        'chat' => $chat,
                    ];

                    // 5. Broadcast Event
                    broadcast(new MessageSendEvent($data))->toOthers();

                    // 6. Send Notification
                    if ($creator && $creator->new_message_notification) {
                        $creator->notify(new MessageNotifications($data));
                    }
                }
            }

            DB::commit();
            return redirect()->to($this->redirectSuccess);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tip Success Error: ' . $e->getMessage());
            // return redirect()->to($this->redirectFail);
        }
    }

    /**
     * Send Tip Cancel Callback
     */
    // public function cancel(Request $request)
    // {
    //     return redirect()->to($this->redirectFail);
    // }
}
