<?php

namespace App\Http\Controllers\Api\V2\Gateway;

use App\Http\Controllers\Controller;
use App\Models\Order;

class PaymentCallbackController extends Controller
{
    public function success($orderId)
    {
        $order = Order::where('id', decrypt($orderId))->firstOrFail();

        $order->forceFill([
            'paid' => true
        ])->save();

        return redirect()->away(config('app.frontend').'/payment/success?order_id='.$order->id);
    }

    public function cancel($orderId)
    {
        $order = Order::where('id', decrypt($orderId))->firstOrFail();

        $order->forceFill([
            'paid' => false
        ])->save();

        return redirect()->away(config('app.frontend').'/payment/failed?order_id='.$order->id);
    }
}