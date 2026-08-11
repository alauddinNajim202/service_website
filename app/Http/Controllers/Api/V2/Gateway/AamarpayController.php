<?php

namespace App\Http\Controllers\Api\V2\Gateway;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AamarpayController extends Controller
{
    public function order($order_id)
    {
        $order = Order::with('product')->find($order_id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }

        $storeId = env('AAMARPAY_STORE_ID');
        $signatureKey = env('AAMARPAY_SIGNATURE_KEY');

        if (empty($storeId) || empty($signatureKey)) {
            throw new RuntimeException('Aamarpay credentials are not configured.');
        }

        try {

            // ✅ Prepare Payload
            $payload = [
                'store_id' => $storeId,
                'signature_key' => $signatureKey,
                'tran_id' => 'order-' . $order->id,

                'success_url' => route('v2.payment.success', ['order_id' => $order->id]),
                'fail_url' => route('v2.payment.cancel', ['order_id' => $order->id]),
                'cancel_url' => route('v2.payment.cancel', ['order_id' => $order->id]),

                'amount' => number_format((float) $order->total, 2, '.', ''),
                'currency' => 'BDT',

                'desc' => 'Order #' . $order->id,

                'cus_name' => $order->customer_name ?? 'Customer',
                'cus_email' => $order->customer_email ?? 'test@mail.com',
                'cus_phone' => $order->customer_phone ?? '01700000000',

                'type' => 'json'
            ];

            Log::info('Aamarpay Payload:', $payload);

            // ✅ API Call
            $response = Http::post('https://sandbox.aamarpay.com/jsonpost.php', $payload);

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Aamarpay request failed',
                    'error' => $response->body()
                ], 500);
            }

            $data = $response->json();

            if (isset($data['result']) && $data['result'] === 'true') {
                return response()->json([
                    'message' => 'Payment initiated',
                    'payment_url' => $data['payment_url']
                ]);
            }

            return response()->json([
                'message' => $data['message'] ?? 'Payment failed',
                'data' => $data
            ], 400);
        } catch (\Exception $e) {

            Log::error('Aamarpay Error:', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Payment failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
