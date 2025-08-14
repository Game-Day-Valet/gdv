<?php

use App\Http\Controllers\BaseController;
use App\Http\Controllers\CouponManagementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BundleController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ChatManagementController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FaqManagementController;
use App\Http\Controllers\PrivacyPolicyManagementController;
use App\Http\Controllers\RentalManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RentalSystem\RentalSystemController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmailPreviewController;

require __DIR__ . '/auth.php';

Route::get('/preview-email', [AuthController::class, 'previewVerifyEmail'])->name('preview.email');
Route::get('/register-referal', [BaseController::class, 'registerReferal'])->name('register.referal');

// Rental System Frontend Routes (Public)
Route::prefix('rental-system')->name('rentalsystem.')->group(function () {
	Route::get('/signup', [RentalSystemController::class, 'showSignup'])->name('signup');
	Route::post('/signup', [RentalSystemController::class, 'signup'])->name('signup.submit');
	Route::get('/signin', [RentalSystemController::class, 'showSignin'])->name('signin');
	Route::post('/signin', [RentalSystemController::class, 'signin'])->name('signin.submit');
	Route::get('/email-verification', [RentalSystemController::class, 'showEmailVerification'])->name('email-verification');
	Route::post('/email-verification', [RentalSystemController::class, 'verifyEmail'])->name('email-verification.submit');
	Route::get('/email-verification/resend', [RentalSystemController::class, 'resendVerificationCode'])->name('email-verification.resend');
	Route::get('/forgot-password', [RentalSystemController::class, 'showForgotPassword'])->name('forgot-password');
	Route::post('/forgot-password', [RentalSystemController::class, 'forgotPassword'])->name('forgot-password.submit');
	Route::get('/auth/google/redirect', [RentalSystemController::class, 'googleRedirect'])->name('google.redirect');
	Route::get('/auth/google/callback', [RentalSystemController::class, 'googleCallback'])->name('google.callback');

	// Protected routes for authenticated users (web session)
	Route::middleware('auth')->group(function () {
		Route::get('/sports', [RentalSystemController::class, 'showSports'])->name('sports');
		Route::get('/sports/{sportId}/tournaments', [RentalSystemController::class, 'showTournaments'])->name('tournaments');
		Route::get('/tournaments/{tournamentId}/rental', [RentalSystemController::class, 'showRentalBooking'])->name('rental-booking');
		Route::post('/rentals', [RentalSystemController::class, 'createRental'])->name('rental.create');
		Route::get('/checkout/success', [RentalSystemController::class, 'checkoutSuccess'])->name('checkout.success');
		Route::get('/checkout/cancel', [RentalSystemController::class, 'checkoutCancel'])->name('checkout.cancel');
		Route::get('/profile', [RentalSystemController::class, 'showProfile'])->name('profile');
		Route::post('/profile', [RentalSystemController::class, 'updateProfile'])->name('profile.update');
		Route::get('/logout', [RentalSystemController::class, 'logout'])->name('logout');
	});
});

Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
	// Route::get('', [RoutingController::class, 'index'])->name('root');
	Route::get('/profile', [RegisteredUserController::class, 'profile'])->name('profile');
	Route::post('/profile/update', [RegisteredUserController::class, 'updateProfile'])->name('profile.update');
	Route::post('/change-password', [RegisteredUserController::class, 'changePassword'])->name('user.change-password');
	Route::get('', [DashboardController::class, 'index'])->name('home');
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
		Route::post('/coupon-management/{id}/send', [CouponManagementController::class, 'send'])->name('coupon-management.send');
		Route::get('/coupon-management/{id}/preview', [CouponManagementController::class, 'preview'])->name('coupon-management.preview');
		Route::resource('faq-management', FaqManagementController::class);
		Route::resource('privacy-policy-management', PrivacyPolicyManagementController::class);

		// Additional rental management routes
		Route::post('/rental-management/{id}/update-status', [RentalManagementController::class, 'updateStatus'])->name('rental-management.update-status');
		Route::post('/rental-management/{id}/update-payment-status', [RentalManagementController::class, 'updatePaymentStatus'])->name('rental-management.update-payment-status');
		Route::get('/rental-management/{id}/available-statuses', [RentalManagementController::class, 'getAvailableStatuses'])->name('rental-management.available-statuses');
	});
});
