<?php

use App\Http\Controllers\CouponManagementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BundleController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ChatManagementController;
use App\Http\Controllers\FaqManagementController;
use App\Http\Controllers\PrivacyPolicyManagementController;
use App\Http\Controllers\RentalManagementController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmailPreviewController;

require __DIR__ . '/auth.php';

Route::get('/preview-email', [AuthController::class, 'previewVerifyEmail'])->name('preview.email');

Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
    Route::get('', [RoutingController::class, 'index'])->name('root');
    Route::get('/profile', [RegisteredUserController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [RegisteredUserController::class, 'updateProfile'])->name('profile.update');
    Route::post('/change-password', [RegisteredUserController::class, 'changePassword'])->name('user.change-password');
    Route::get('/home', [DashboardController::class, 'index'])->name('home');
    // Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    // Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    // Route::get('{any}', [RoutingController::class, 'root'])->name('any');

    // Chat Management Routes
    Route::get('/chat-management', [ChatManagementController::class, 'index'])->name('chat-management.index');
    Route::get('/chat-management/{id}', [ChatManagementController::class, 'show'])->name('chat-management.show');
    Route::post('/chat-management/{id}/send', [ChatManagementController::class, 'sendMessage'])->name('chat-management.send');
    Route::get('/chat-management/{id}/messages', [ChatManagementController::class, 'getMessages'])->name('chat-management.messages');
    Route::post('/chat-management/{id}/mark-read', [ChatManagementController::class, 'markAsRead'])->name('chat-management.mark-read');
    Route::post('/chat-management/{id}/close', [ChatManagementController::class, 'closeConversation'])->name('chat-management.close');
    Route::get('/chat-management/unassigned/list', [ChatManagementController::class, 'getUnassignedConversations'])->name('chat-management.unassigned');

        // User Management
    Route::group(['middleware' => ['can:super_admin']], function () {
        Route::resource('user-management', UserController::class);
        Route::resource('role-management', RoleController::class);
        Route::resource('sport-management', SportController::class);
        Route::resource('tournament-management', TournamentController::class);
        Route::resource('item-management', ItemController::class);
        Route::resource('bundle-management', BundleController::class);
        Route::resource('rental-management', RentalManagementController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('coupon-management', CouponManagementController::class);
        Route::resource('faq-management', FaqManagementController::class);
        Route::resource('privacy-policy-management', PrivacyPolicyManagementController::class);

        // Additional rental management routes
        Route::post('/rental-management/{id}/update-status', [RentalManagementController::class, 'updateStatus'])->name('rental-management.update-status');
        Route::post('/rental-management/{id}/update-payment-status', [RentalManagementController::class, 'updatePaymentStatus'])->name('rental-management.update-payment-status');
    });
});
