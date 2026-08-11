<?php

use App\Http\Controllers\Api\V1\Auth\SocialLoginController;
use App\Http\Controllers\Web\Frontend\AffiliateController;
use App\Http\Controllers\Web\Frontend\ContactController;
use App\Http\Controllers\Web\Frontend\HomeController;
use App\Http\Controllers\Web\Frontend\PageController;
use App\Http\Controllers\Web\Frontend\SubscriberController;
use App\Http\Controllers\Web\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
})->name('home');

Route::get('/verify-email', [\App\Http\Controllers\Api\V1\Auth\RegisterController::class, 'verifyEmail'])->name('verify.email');

Route::get('/reset-password-link/{user}', [\App\Http\Controllers\Api\V1\Auth\ResetPasswordController::class, 'verifyEmailLink'])->name('verify.email.link');

Route::get('/affiliate/{slug}',[AffiliateController::class, 'store'])->name('store');

Route::get('/post',[HomeController::class, 'index'])->name('post.index');
Route::get('/post/show/{slug}',[HomeController::class, 'post'])->name('post.show');

Route::get('social-login/{provider}',[SocialLoginController::class,'RedirectToProvider'])->name('social.login');
Route::get('social-login/{provider}/callback',[SocialLoginController::class, 'HandleProviderCallback']);

Route::post('subscriber/store',[SubscriberController::class, 'store'])->name('subscriber.data.store');

Route::post('contact/store',[ContactController::class, 'store'])->name('contact.store');

Route::controller(NotificationController::class)->prefix('notification')->name('notification.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('read/single/{id}', 'readSingle')->name('read.single');
    Route::POST('read/all', 'readAll')->name('read.all');
})->middleware('auth');

require __DIR__.'/auth.php';

require __DIR__.'/sitemap.php';

Route::get('/page/{slug}',[PageController::class, 'index']);

