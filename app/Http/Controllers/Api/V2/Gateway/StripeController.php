<?php

namespace App\Http\Controllers\Api\V2\Gateway;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use RuntimeException;

class StripeController extends Controller
{
    public function order($order_id)
    {
        $order = Order::with('product')->find($order_id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }

        $secret = config('services.stripe.secret_key');

        if (empty($secret)) {
            throw new RuntimeException('Stripe credentials are not configured.');
        }

        // Set API key
        Stripe::setApiKey($secret);

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],

                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => [
                                'name' => $order->product->name ?? 'Order #' . $order->id,
                            ],
                            'unit_amount' => (int) ($order->total * 100), // cents
                        ],
                        'quantity' => 1,
                    ],
                ],

                'mode' => 'payment',

                'success_url' => route('v2.payment.success', [
                    'order_id' => $order->id
                ]),

                'cancel_url' => route('v2.payment.cancel', [
                    'order_id' => $order->id
                ]),
            ]);

            return response()->json([
                'id' => $session->id,
                'url' => $session->url, // redirect this in frontend
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Stripe session creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
