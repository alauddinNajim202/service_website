<?php

namespace App\Http\Controllers\Api\V1\Gateway\Stripe;

use Stripe\Stripe;
use Stripe\Customer;
use App\Http\Controllers\Controller;

class StripeCustomerController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }
    
    public function createCustomer()
    {
        $user = auth('api')->user();
        if ($user->stripe_customer_id == null) {
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name,
            ]);

            $user->update(['stripe_customer_id' => $customer->id]);
        }

        return $user->stripe_customer_id;
    }
    
}
