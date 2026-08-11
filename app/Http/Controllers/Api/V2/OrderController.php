<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    function order($order_id)
    {
        $order = \App\Models\Order::with('product')->find($order_id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        return response()->json(['order' => $order]);
    }
}
