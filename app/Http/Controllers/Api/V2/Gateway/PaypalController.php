<?php

namespace App\Http\Controllers\Api\V2\Gateway;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use RuntimeException;

class PaypalController extends Controller
{
    public function order($order_id)
    {
        $order = Order::with('product')->find($order_id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }

        $client = env("PAYPAL_CLIENT_ID");
        $secret = env("PAYPAL_SECRET");

        if (empty($client) || empty($secret)) {
            throw new RuntimeException('PayPal credentials are not configured.');
        }

        $base = 'https://api-m.sandbox.paypal.com';

        // Step 1: Get Access Token
        $tokenResponse = Http::asForm()
            ->withBasicAuth($client, $secret)
            ->post($base . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$tokenResponse->successful()) {
            return response()->json([
                'message' => 'Failed to get PayPal token',
                'error' => $tokenResponse->body()
            ], 500);
        }

        $accessToken = $tokenResponse->json()['access_token'];

        // Step 2: Create Order
        $response = Http::acceptJson()
            ->withToken($accessToken)
            ->withHeaders([
                'PayPal-Request-Id' => 'order-' . $order->id,
            ])
            ->post($base . '/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => (string) $order->id,
                        'custom_id' => (string) $order->id,
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => number_format((float) $order->total, 2, '.', ''),
                        ],
                    ],
                ],
                'application_context' => [
                    'return_url' => route('v2.payment.success', ['order_id' => $order->id]),
                    'cancel_url' => route('v2.payment.cancel', ['order_id' => $order->id]),
                    'shipping_preference' => 'NO_SHIPPING',
                ],
            ]);

        if (!$response->successful()) {
            return response()->json([
                'message' => 'Failed to create PayPal order',
                'error' => $response->body()
            ], 500);
        }

        return response()->json($response->json());
    }
}
