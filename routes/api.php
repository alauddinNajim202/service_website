<?php

use App\Http\Controllers\Api\FavouriteController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Auth\SocialLoginController;
use App\Http\Controllers\Api\V1\Auth\UserController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\GeminiImageController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\FirebaseTokenController;
use App\Http\Controllers\Api\V1\Frontend\CategoryController;
use App\Http\Controllers\Api\V1\Frontend\CreatorController;
use App\Http\Controllers\Api\V1\Frontend\FaqController;
use App\Http\Controllers\Api\V1\Frontend\HomeController;
use App\Http\Controllers\Api\V1\Frontend\ImageController;
use App\Http\Controllers\Api\V1\Frontend\PageController;
use App\Http\Controllers\Api\V1\Frontend\PostController;
use App\Http\Controllers\Api\V1\Frontend\SettingsController;
use App\Http\Controllers\Api\V1\Frontend\SocialLinksController;
use App\Http\Controllers\Api\V1\Frontend\SubcategoryController;
use App\Http\Controllers\Api\V1\Frontend\SubscriberController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PrayerTimesController;
use App\Http\Controllers\Api\V1\SessionPackageController;
use App\Http\Controllers\Api\V1\CreatorDashboardController;
use App\Http\Controllers\Api\V1\UserDashboardController;
use App\Http\Controllers\Api\V2\Gateway\PaymentCallbackController;
use App\Http\Controllers\Api\V2\OrderController as APIOrderControllerV2;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'guest:api'], function ($router) {
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('creator-register', [RegisterController::class, 'creatorRegister']);
    Route::get('/verify-email', [RegisterController::class, 'verifyEmail'])->name('verify.email');
    Route::post('/resend-otp', [RegisterController::class, 'ResendOtp']);
    Route::post('/verify-otp', [RegisterController::class, 'VerifyEmail']);
    // login
    Route::post('login', [LoginController::class, 'login'])->name('api.login');
    // forgot password
    Route::post('/forget-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/otp-token', [ResetPasswordController::class, 'MakeOtpToken']);
    Route::post('/reset-password', [ResetPasswordController::class, 'ResetPassword']);
    // social login
    Route::post('/social-login', [SocialLoginController::class, 'SocialLogin']);
});

Route::name('v1')->group(function () {

    Route::get('/page/home', [HomeController::class, 'index']);

    Route::get('/category', [CategoryController::class, 'index'])->name('category');
    Route::get('/subcategory', [SubcategoryController::class, 'index'])->name('subcategory');

    Route::get('/social/links', [SocialLinksController::class, 'index'])->name('social.links');
    
    // Legal Documents
    Route::get('/legal-documents', [App\Http\Controllers\Api\V1\Frontend\LegalDocumentApiController::class, 'index'])->name('legal-documents');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::get('/faq', [FaqController::class, 'index'])->name('faq');
    Route::get('/top-creators', [CreatorController::class, 'topCreators'])->name('top.creators');
    Route::get('/creators', [CreatorController::class, 'index'])->name('creators');
    Route::get('/creators/{id}', [CreatorController::class, 'show'])->name('creators.show');

    Route::post('subscriber/store', [SubscriberController::class, 'store'])->name('api.subscriber.store');

    /*
    # Post
    */
    Route::middleware(['auth:api'])->controller(PostController::class)->prefix('auth/post')->group(function () {
        Route::get('/', 'index');
        Route::post('/store', 'store')->name('post.store');
        Route::get('/show/{id}', 'show')->name('post.show');
        Route::post('/update/{id}', 'update')->name('post.update');
        Route::delete('/delete/{id}', 'destroy')->name('post.delete');
    });

    Route::get('/posts', [PostController::class, 'posts'])->name('post.posts');
    Route::get('/post/show/{post_id}', [PostController::class, 'post'])->name('post.post');

    Route::middleware(['auth:api'])->controller(ImageController::class)->prefix('auth/post/image')->group(function () {
        Route::get('/', 'index')->name('image.index');
        Route::post('/store', 'store')->name('image.store');
        Route::get('/delete/{id}', 'destroy')->name('image.delete');
    });

    Route::get('dynamic/page', [PageController::class, 'index'])->name('page.index');
    Route::get('dynamic/page/show/{slug}', [PageController::class, 'show'])->name('page.show');

    /*

    */

    Route::group(['middleware' => ['auth:api', 'api-otp']], function ($router) {
        Route::get('/refresh-token', [LoginController::class, 'refreshToken'])->name('refresh.token');
        Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
        Route::get('/me', [UserController::class, 'me'])->name('me');
        Route::get('/account/switch', [UserController::class, 'accountSwitch'])->name('account.switch');
        Route::post('/update-profile', [UserController::class, 'updateProfile'])->name('update.profile');
        Route::post('/update-creator-profile', [UserController::class, 'updateCreatorProfile'])->name('update.creator.profile');
        Route::post('/update-password', [UserController::class, 'updatePassword'])->name('update.password');
        Route::post('/update-avatar', [UserController::class, 'updateAvatar'])->name('update.avatar');
        Route::delete('/delete-profile', [UserController::class, 'destroy'])->name('delete.profile');
        Route::post('/favourite/toggle', [FavouriteController::class, 'toggle'])->name('favourite.toggle');
        Route::get('/favourites', [FavouriteController::class, 'index'])->name('favourites');
        Route::get('/notification/settings', [UserController::class, 'getNotificationSettings'])->name('notification.settings');
        Route::post('/notification/settings', [UserController::class, 'updateNotificationSettings'])->name('update.notification.settings');
        
        // Creator Dashboard
        Route::get('/creator/dashboard', [CreatorDashboardController::class, 'index'])->name('creator.dashboard');
        Route::get('/creator/dashboard/recent-earnings', [CreatorDashboardController::class, 'recentEarnings'])->name('creator.dashboard.recent.earnings');

        // User Dashboard
        Route::get('/user/sessions', [UserDashboardController::class, 'index'])->name('user.sessions');
        Route::get('/user/transactions', [UserDashboardController::class, 'transactions'])->name('user.transactions');
        Route::get('/user/billing', [UserDashboardController::class, 'transactions'])->name('user.billing');
        Route::get('/user/billings', [UserDashboardController::class, 'transactions'])->name('user.billings');
    });

    /*
    # Firebase Notification Route
    */

    Route::middleware(['auth:api'])->controller(FirebaseTokenController::class)->prefix('firebase')->group(function () {
        Route::get('test', 'test')->name('test');
        Route::post('token/add', 'store')->name('token.add');
        Route::post('token/get', 'getToken')->name('token.get');
        Route::post('token/delete', 'deleteToken')->name('token.delete');
    });

    /*
    # In App Notification Route
    */

    Route::middleware(['auth:api'])->controller(NotificationController::class)->prefix('notify')->group(function () {
        Route::get('test', 'test')->name('test');
        Route::get('/', 'index')->name('index');
        Route::post('read/{id?}', 'read')->name('read');
    });

    /*
    # Chat Route
    */

    Route::middleware(['auth:api'])->controller(ChatController::class)->prefix('auth/chat')->group(function () {
        Route::get('/list', 'list')->name('list');
        Route::post('/send/{receiver_id}', 'send')->name('send');
        Route::get('/conversation/{receiver_id}', 'conversation')->name('conversation');
        Route::get('/room/{receiver_id}', 'room')->name('room');
        Route::get('/search', 'search')->name('search');
        Route::get('/seen/all/{receiver_id}', 'seenAll')->name('seen.all');
        Route::get('/seen/single/{chat_id}', 'seenSingle')->name('seen.single');
        Route::get('/pinned-list', 'pinnedList')->name('pinned.list');
        Route::post('/pin/{user_id}', 'togglePin')->name('toggle.pin');
        Route::delete('/conversation/{receiver_id}', 'deleteConversation')->name('delete.conversation');
        Route::post('/block/{user_id}', 'toggleBlock')->name('toggle.block');
        Route::get('/blocked-list', 'blockedList')->name('blocked.list');
    });

    /*
    # Gemini Image Generation Route
    */
    Route::middleware(['auth:api'])->post('/gemini/generate-image', [GeminiImageController::class, 'generate']);

    /*
    # Session Package Route
    */
    Route::middleware(['auth:api'])->group(function () {
        Route::get('/session-packages', [SessionPackageController::class, 'index']);
        Route::post('/session-packages/vip/price', [SessionPackageController::class, 'updateVipPrice'])->middleware('api-retailer');
        
        // Book Session & Stripe Checkout
        Route::post('/book-session/checkout', [\App\Http\Controllers\Api\V1\BookSessionController::class, 'checkout'])->name('api.book-session.checkout');
        
        // Session Presence Tracking
        Route::post('/session/join', [\App\Http\Controllers\Api\V1\SessionPresenceController::class, 'join']);
        Route::post('/session/leave', [\App\Http\Controllers\Api\V1\SessionPresenceController::class, 'leave']);
    });

    Route::get('/book-session/payment/success', [\App\Http\Controllers\Api\V1\BookSessionController::class, 'success'])->name('api.book-session.success');
    Route::get('/book-session/payment/cancel', [\App\Http\Controllers\Api\V1\BookSessionController::class, 'cancel'])->name('api.book-session.cancel');

    /*
    # CMS
    */

    Route::prefix('cms')->name('cms.')->group(function () {
        Route::get('home', [HomeController::class, 'index'])->name('home');
    });

    /*
    # prayer time
    # http:://127.0.0.1:8000/api/prayer-times?date=2025-12-25&lat=23.7018&lng=90.3742&timezone=6&method=1

    */
    Route::prefix('prayer-times')->group(function () {
        Route::get('/', [PrayerTimesController::class, 'index']);
        Route::get('/today', [PrayerTimesController::class, 'today']);
        Route::get('/methods', [PrayerTimesController::class, 'methods']);
    });

    Route::post('contact/store', [ContactController::class, 'store'])->name('contact.store');

    /*
    # test code
    */
    Route::get('/users', [UserController::class, 'users']);

    Route::get('telegram/messages', function () {

        $token = config('services.telegram.token');
        $chatId = config('services.telegram.channel');

        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        Http::post($url, [
            'chat_id' => $chatId,
            'text' => 'hello from laravel 123',
            'parse_mode' => 'HTML',
        ]);
    });

    Route::get('/user-by-name', function (Request $request) {
        $name = $request->input('name');

        return User::where('name', 'LIKE', "%$name%")
            ->select('name', 'email')
            ->get();
    });

    Route::get('/user-email', function (Request $request) {

        $name = $request->input('name');

        $users = User::where('name', 'LIKE', "%$name%")
            ->select('name', 'email')
            ->get();

        // Gemini API call
        $response = Http::post('https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent?key=gen-lang-client-0194670477', [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => 'User data: '.$users->toJson().'.
                            Give a clean response with name and email.',
                        ],
                    ],
                ],
            ],
        ]);

        return $response->json();
    });

});

Route::prefix('v2')->name('v2')->group(function () {
    Route::get('/product/order/{order_id}', [APIOrderControllerV2::class, 'order']);
    Route::get('/payment/success/{order_id}', [PaymentCallbackController::class, 'success']);
    Route::get('/payment/cancel/{order_id}', [PaymentCallbackController::class, 'cancel']);
});
