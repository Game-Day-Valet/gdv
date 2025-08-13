<?php

use App\Enums\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BundleController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\PrivacyPolicyController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RentalController;
use App\Http\Controllers\Api\SportController;
use App\Http\Controllers\Api\TournamentController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\StripeController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/reset', [AuthController::class, 'passwordResetRequest']);
Route::post('/password/reset/confirm', [AuthController::class, 'passwordResetConfirm']);
Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);


Route::post('/email/verify-otp', [AuthController::class, 'verifyOtp']);


Route::post('/validate/referral-code', [AuthController::class, 'checkReferralCode']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user/referral-code', [AuthController::class, 'getReferralCode']);


    Route::post('/profile', [ProfileController::class, 'update']);

    // Tournaments Module
    Route::get('/tournaments', [TournamentController::class, 'index']);
    Route::post('/tournaments', [TournamentController::class, 'store']);
    Route::put('/tournaments/{id}', [TournamentController::class, 'update']);
    Route::delete('/tournaments/{id}', [TournamentController::class, 'destroy']);


    // Sports Module
    Route::get('/sports', [SportController::class, 'index']);
    Route::post('/sports', [SportController::class, 'store']);
    Route::put('/sports/{id}', [SportController::class, 'update']);
    Route::delete('/sports/{id}', [SportController::class, 'destroy']);
    Route::get('/sports/tournaments/{id}', [SportController::class, 'tournaments']);


    // Items Module
    Route::get('/items', [ItemController::class, 'index']);
    Route::post('/items', [ItemController::class, 'store']);
    Route::put('/items/{id}', [ItemController::class, 'update']);
    Route::delete('/items/{id}', [ItemController::class, 'destroy']);


    // Bundle Module
    Route::get('/bundles', [BundleController::class, 'index']);
    Route::post('/bundles', [BundleController::class, 'store']);
    Route::put('/bundles/{id}', [BundleController::class, 'update']);
    Route::delete('/bundles/{id}', [BundleController::class, 'destroy']);


    // Coupon Module
    Route::get('/coupons', [CouponController::class, 'index']);
    Route::post('/coupon/validate', [CouponController::class, 'validateCoupon']);


    // Rental Module
    Route::get('/rentals', [RentalController::class, 'index']);
    Route::get('/rentals/user', [RentalController::class, 'userRentals']);
    Route::post('/rentals', [RentalController::class, 'store']);
    Route::put('/rentals/{id}', [RentalController::class, 'update']);
    Route::delete('/rentals/{id}', [RentalController::class, 'destroy']);


    // Favorites Module
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);


    // Cart Module
    Route::post('/cart', [CartController::class, 'store']);
    Route::get('/cart', [CartController::class, 'index']);


    // FAQ Module
    Route::get('/faqs', [FaqController::class, 'index']);
    Route::post('/faqs', [FaqController::class, 'store']);
    Route::put('/faqs/{id}', [FaqController::class, 'update']);
    Route::delete('/faqs/{id}', [FaqController::class, 'destroy']);

    // Privacy Policy Module
    Route::get('/privacy-policies', [PrivacyPolicyController::class, 'index']);


    // Chat Module
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    Route::post('/chat/reply/{conversationId}', [ChatController::class, 'replyToMessage']);
    Route::get('/chat/conversations', [ChatController::class, 'getConversations']);
    Route::get('/chat/conversations/{conversationId}/messages', [ChatController::class, 'getMessages']);
    Route::post('/chat/conversations/{conversationId}/mark-read', [ChatController::class, 'markAsRead']);
    Route::post('/chat/conversations/{conversationId}/close', [ChatController::class, 'closeConversation']);
    Route::get('/chat/conversations/{conversationId}/details', [ChatController::class, 'getConversationDetails']);
    Route::get('/chat/unassigned', [ChatController::class, 'getUnassignedConversations']);

    Route::post('/create-payment-intent', [StripeController::class, 'createPaymentIntent']);
});


// Sign in with Google
// Route::get('/auth/google/redirect', [AuthController::class, 'googleRedirect']);
// Route::get('/auth/google/callback', [AuthController::class, 'googleCallback']);
// Route::post('/auth/google/login', [AuthController::class, 'googleLogin']);
Route::post('/auth/google/login', [AuthController::class, 'googleLogin']);


// Sign in with Apple
Route::get('/auth/apple/redirect', [AuthController::class, 'appleRedirect']);
Route::get('/auth/apple/callback', [AuthController::class, 'appleCallback']);
Route::post('/auth/apple/login', [AuthController::class, 'appleLogin']);
