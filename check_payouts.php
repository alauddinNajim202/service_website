<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Stripe\Stripe::setApiKey(config('services.stripe.secret'));

try {
    $payouts = Stripe\Payout::all(['limit' => 5], ['stripe_account' => 'acct_1OYpLBCzsYUhDGrW']);
    foreach($payouts->data as $p) {
        echo 'Payout ID: ' . $p->id . ' | Amount: ' . ($p->amount/100) . ' ' . $p->currency . ' | Status: ' . $p->status . PHP_EOL;
    }
    if (count($payouts->data) === 0) {
        echo "No payouts found on this connected account.\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
