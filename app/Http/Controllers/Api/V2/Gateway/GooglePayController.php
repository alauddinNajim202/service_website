<?php

namespace App\Http\Controllers\Api\V2\Gateway;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use RuntimeException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class GooglePayController extends Controller
{
    public function order(Request $request, $order_id)
    {
        $order = Order::with('product')->find($order_id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }

        $token = $request->token;
        if (empty($token)) {
            return response()->json([
                'message' => 'Payment token is required'
            ], 400);
        }

        $secret = config('services.stripe.secret_key');

        if (empty($secret)) {
            throw new RuntimeException('Stripe credentials are not configured.');
        }

        Stripe::setApiKey($secret);

        try {
            $intent = PaymentIntent::create([
                'amount' => (int) round($order->total * 100), // cents
                'currency' => 'eur',
                'payment_method_data' => [
                    'type' => 'card',
                    'card' => [
                        'token' => $token,
                    ],
                ],
                'confirm' => true,
                'payment_method_types' => ['card'],
                'description' => 'Order #' . $order->id . ' - Google Pay',
                'metadata' => [
                    'order_id' => $order->id,
                ],
            ]);

            if ($intent->status !== 'succeeded') {
                return response()->json([
                    'message' => 'Payment not completed',
                    'status' => $intent->status
                ], 400);
            }

            $order->update(['status' => 'paid']);

            return response()->json([
                'message' => 'Payment successful',
                'data' => $intent
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Stripe payment failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
