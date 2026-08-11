<?php

use App\Http\Controllers\Api\V1\SessionPackageController;
use Illuminate\Support\Facades\Route;

Route::put('/session-packages/vip/price', [SessionPackageController::class, 'updateVipPrice'])
    ->name('api.retailer.session-packages.vip.price');
