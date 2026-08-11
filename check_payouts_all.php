<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Stripe\Stripe::setApiKey(config('services.stripe.secret'));

$users = App\Models\User::whereNotNull('stripe_account_id')->get();
foreach($users as $u) {
    echo "User " . $u->email . " has account: " . $u->stripe_account_id . "\n";
    try {
        $payouts = Stripe\Payout::all(['limit' => 5], ['stripe_account' => $u->stripe_account_id]);
        foreach($payouts->data as $p) {
            echo '  -> Payout ID: ' . $p->id . ' | Amount: ' . ($p->amount/100) . ' ' . $p->currency . ' | Status: ' . $p->status . PHP_EOL;
        }
    } catch (Exception $e) {
        echo '  -> Error: ' . $e->getMessage() . "\n";
    }
}
