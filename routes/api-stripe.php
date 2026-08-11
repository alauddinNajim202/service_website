<?php

use App\Http\Controllers\Api\V1\Gateway\Stripe\StripeCallBackController;
use App\Http\Controllers\Api\V1\Gateway\Stripe\StripeOnBoardingController;
use App\Http\Controllers\Api\V1\Gateway\Stripe\StripeSubscriptionsController;
use App\Http\Controllers\Api\V1\Gateway\Stripe\StripeWebHookController;
use App\Http\Controllers\Api\V1\Gateway\Stripe\StripeWebHookHoldController;
use App\Http\Controllers\Api\V1\Gateway\Stripe\StripeWebHookSpliteController;
use Illuminate\Support\Facades\Route;

/*
# Stripe routes
*/

Route::prefix('api')->name('api.')->group(function () {

    Route::controller(StripeCallBackController::class)->prefix('payment/stripe')->name('payment.stripe.')->group(function () {
        Route::post('/checkout', 'checkout')->middleware(['auth:api']);
        Route::get('/success', 'success')->name('success');
        Route::get('/cancel', 'failure')->name('cancel');
    });

    Route::controller(StripeWebHookController::class)->prefix('payment/stripe')->name('payment.stripe.')->group(function () {
        Route::post('/intent', 'intent')->middleware(['auth:api']);
        Route::post('/webhook', 'webhook');
    });

    // stripe split webhook
    Route::controller(StripeWebHookSpliteController::class)->prefix('payment/stripe/split')->name('payment.stripe.split.')->group(function () {
        Route::get('/intent/{booking_id}', 'intent')->name('intent');
        Route::post('/webhook', 'webhook')->name('webhook');
    });

    // stripe account
    Route::controller(StripeOnBoardingController::class)->prefix('payment/stripe/account')->name('payment.stripe.account.')->group(function () {
        Route::middleware(['auth:api'])->post('/connect', 'accountConnect')->name('connect');
        Route::get('/connect/success/{account_id}', 'accountSuccess')->name('connect.success');
        Route::get('/connect/refresh/{account_id}', 'accountRefresh')->name('connect.refresh');
        Route::middleware(['auth:api'])->get('/url', 'AccountUrl')->name('url');
        Route::middleware(['auth:api'])->get('/info', 'accountInfo')->name('info');
        Route::middleware(['auth:api'])->post('/withdraw', 'withdraw')->name('withdraw');
        Route::middleware(['auth:api'])->get('/withdraw/history', 'getWithdrawalsHistory')->name('withdraw.history');
        
        // bank accounts
        Route::middleware(['auth:api'])->get('/banks', 'listBanks')->name('banks');
        Route::middleware(['auth:api'])->post('/bank', 'addBank')->name('bank.add');
        Route::middleware(['auth:api'])->post('/bank/{bank_id}', 'setDefaultBank')->name('bank.default');
        Route::middleware(['auth:api'])->delete('/bank/{bank_id}', 'deleteBank')->name('bank.delete');
        Route::middleware(['auth:api'])->delete('/stripe-account', 'deleteAccount')->name('stripe-account.delete');
    });

    // stripe subscriptions
    Route::controller(StripeSubscriptionsController::class)->prefix('payment/stripe/subscriptions')->name('payment.stripe.subscriptions.')->group(function () {
        Route::post('/plan', 'plan');
        Route::get('/my/plan', 'myPlan');
        Route::get('/cancel/plan', 'cancelPlan');
    });

    Route::controller(StripeWebHookHoldController::class)->prefix('payment/stripe/payment/hold')->name('payment.stripe.payment.hold.')->group(function () {
        Route::post('/create', 'createPaymentHold')->name('create');
        Route::post('/capture', 'capturePayment')->name('capture');
        Route::post('/cancel', 'cancelPaymentHold')->name('cancel');
    });

});

Route::prefix('api/v2')->name('api.v2')->group(function () {});
