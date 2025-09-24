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
use App\Http\Controllers\TermsConditionManagementController;
use App\Http\Controllers\RentalManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RentalSystem\RentalSystemController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmailPreviewController;
use App\Http\Controllers\RentalArchiveController;
use App\Http\Controllers\TwilioChatController;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/auth.php';

// Root route - redirect unauthenticated users to rentalsystem signin
// Route::get('/', function () {
//     if (!\Illuminate\Support\Facades\Auth::check()) {
//         return redirect()->route('rentalsystem.signin');
//     }
//     return redirect()->route('rentalsystem.sports');
// })->name('root');

Route::get('/preview-email', [AuthController::class, 'previewVerifyEmail'])->name('preview.email');
Route::get('/register-referral', [BaseController::class, 'registerReferal'])->name('register.referal');

// Rental System Frontend Routes (Public)
Route::prefix('')->name('rentalsystem.')->group(function () {
	// Public routes - no authentication required
	Route::get('', [RentalSystemController::class, 'showSports'])->name('sports');
	Route::get('/sports/{sportId}/tournaments', [RentalSystemController::class, 'showTournaments'])->name('tournaments');
	Route::get('/tournaments/{tournamentId}/details', [RentalSystemController::class, 'showTournamentDetails'])->name('tournament.details');
	// Allow anonymous to open booking page and submit; controller will enforce login on submit
	Route::get('/tournaments/{tournamentId}/rental', [RentalSystemController::class, 'showRentalBooking'])->name('rental-booking');
	Route::post('/rentals', [RentalSystemController::class, 'createRental'])->name('rental.create');

	// Authentication routes
	// Route::get('/signup', [RentalSystemController::class, 'showSignup'])->name('signup');
	// Route::post('/signup', [RentalSystemController::class, 'signup'])->name('signup.submit');
	// Route::get('/signin', [RentalSystemController::class, 'showSignin'])->name('signin');
	// Route::post('/signin', [RentalSystemController::class, 'signin'])->name('signin.submit');
	// Route::post('/google-login', [RentalSystemController::class, 'googleLogin'])->name('google.login');
	// Route::get('/email-verification', [RentalSystemController::class, 'showEmailVerification'])->name('email-verification');
	// Route::post('/email-verification', [RentalSystemController::class, 'verifyEmail'])->name('email-verification.submit');
	// Route::get('/email-verification/resend', [RentalSystemController::class, 'resendVerificationCode'])->name('email-verification.resend');
	// Route::get('/forgot-password', [RentalSystemController::class, 'showForgotPassword'])->name('forgot-password');
	// Route::post('/forgot-password', [RentalSystemController::class, 'forgotPassword'])->name('forgot-password.submit');
	// Route::get('/reset-password/code', [RentalSystemController::class, 'showResetCode'])->name('reset-password.code');
	// Route::post('/reset-password/code', [RentalSystemController::class, 'verifyResetCode'])->name('reset-password.code.submit');
	// Route::get('/reset-password/new', [RentalSystemController::class, 'showResetPassword'])->name('reset-password.new');
	// Route::post('/reset-password/new', [RentalSystemController::class, 'resetPassword'])->name('reset-password.submit');


	// Protected routes for authenticated users only
	
	
	// Route::middleware('auth')->group(function () {
	
	Route::get('/checkout/success', [RentalSystemController::class, 'checkoutSuccess'])->name('checkout.success');
	Route::get('/checkout/cancel', [RentalSystemController::class, 'checkoutCancel'])->name('checkout.cancel');
	// Hide profile endpoints

	// Route::post('/profile', [RentalSystemController::class, 'updateProfile'])->name('profile.update');
	// Route::get('/profile', [RentalSystemController::class, 'showProfile'])->name('profile');
	// Route::post('/profile/notifications', [RentalSystemController::class, 'updateNotifications'])->name('profile.notifications');
	// Route::delete('/profile/delete-account', [RentalSystemController::class, 'deleteAccountWeb'])->name('profile.delete');
	// Route::get('/logout', [RentalSystemController::class, 'logout'])->name('logout');

	// });
	// Allow profile page to handle auth gracefully itself
});

// Public page: Privacy Policy for rental system (rendered server-side for SEO and reliability)
Route::get('/privacy-policy', [\App\Http\Controllers\RentalSystem\RentalSystemController::class, 'privacyPolicy'])->name('rentalsystem.privacy-policy');

// Public Twilio chat media (no auth, direct HTTPS for Twilio)
Route::get('/twilio/chat/media/{filename}', [\App\Http\Controllers\TwilioChatController::class, 'media'])->name('twilio.chat.media');

// Public page: Support
Route::view('/support', 'rentalsystem.support')->name('rentalsystem.support');
// Public page: Terms & Conditions
Route::get('/terms', [\App\Http\Controllers\RentalSystem\RentalSystemController::class, 'terms'])->name('rentalsystem.terms');
// Public page: Account Delete Policy
Route::view('/account-delete-policy', 'rentalsystem.account-delete-policy')->name('rentalsystem.account-delete-policy');

Route::group(['prefix' => 'admin', 'middleware' => ['auth', function ($request, $next) {
	$user = \Auth::user();
	if (!$user || (!$user->hasRole(\App\Enums\Role::MANAGER) && !$user->hasRole(\App\Enums\Role::SUPER_ADMIN))) {
		return redirect()->route('rentalsystem.signin')->with('error', 'Access denied: Admin panel is restricted to managers and administrators.');
	}
	return $next($request);
}]], function () {
	// Route::get('', [RoutingController::class, 'index'])->name('root');
	Route::get('/profile', [RegisteredUserController::class, 'profile'])->name('profile');
	Route::post('/profile/update', [RegisteredUserController::class, 'updateProfile'])->name('profile.update');
	Route::post('/change-password', [RegisteredUserController::class, 'changePassword'])->name('user.change-password');
	Route::get('', [DashboardController::class, 'index'])->name('home');
	// Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
	// Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
	// Route::get('{any}', [RoutingController::class, 'root'])->name('any');

	// Chat Management Routes (disabled)
	// Route::get('/chat-management', [ChatManagementController::class, 'index'])->name('chat-management.index');
	// Route::get('/chat-management/{id}', [ChatManagementController::class, 'show'])->name('chat-management.show');
	// Route::post('/chat-management/{id}/send', [ChatManagementController::class, 'sendMessage'])->name('chat-management.send');
	// Route::get('/chat-management/{id}/messages', [ChatManagementController::class, 'getMessages'])->name('chat-management.messages');
	// Route::post('/chat-management/{id}/mark-read', [ChatManagementController::class, 'markAsRead'])->name('chat-management.mark-read');
	// Route::post('/chat-management/{id}/close', [ChatManagementController::class, 'closeConversation'])->name('chat-management.close');
	// Route::get('/chat-management/unassigned/list', [ChatManagementController::class, 'getUnassignedConversations'])->name('chat-management.unassigned');

	// Sports and Tournaments - Accessible to both manager and admin
	Route::resource('sport-management', SportController::class);
	Route::post('sport-management/reorder', [SportController::class, 'reorder'])->middleware('can:super_admin')->name('sport-management.reorder');
	Route::resource('tournament-management', TournamentController::class);
	Route::post('tournament-management/reorder', [TournamentController::class, 'reorder'])->middleware('can:super_admin')->name('tournament-management.reorder');



	// Rental Management - Accessible to both manager and admin
	Route::get('rental-management/pending', [RentalManagementController::class, 'pending'])->name('rental-management.pending');
	Route::resource('rental-management', RentalManagementController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);

	// Archive
	Route::get('rental-archive', [RentalArchiveController::class, 'index'])->name('rental-archive.index');
	Route::get('rental-archive/folder/{tournamentId}', [RentalArchiveController::class, 'folder'])->name('rental-archive.folder');
	Route::post('rental-archive/archive', [RentalArchiveController::class, 'archive'])->name('rental-archive.archive');
	Route::post('rental-archive/unarchive', [RentalArchiveController::class, 'unarchive'])->name('rental-archive.unarchive');
	Route::post('/rental-management/{id}/update-status', [RentalManagementController::class, 'updateStatus'])->name('rental-management.update-status');
	Route::post('/rental-management/{id}/update-payment-status', [RentalManagementController::class, 'updatePaymentStatus'])->name('rental-management.update-payment-status');
	Route::get('/rental-management/{id}/available-statuses', [RentalManagementController::class, 'getAvailableStatuses'])->name('rental-management.available-statuses');
	Route::post('rental-management/reorder', [RentalManagementController::class, function (\Illuminate\Http\Request $request) {
		$validated = $request->validate(['orders' => 'required|array', 'orders.*.id' => 'required|integer|exists:rentals,id', 'orders.*.sort_order' => 'required|integer|min:0']);
		DB::transaction(function () use ($validated) {
			foreach ($validated['orders'] as $o) {
				\App\Models\Rental::where('id', $o['id'])->update(['sort_order' => (int)$o['sort_order']]);
			}
		});
		return response()->json(['success' => true]);
	}])->middleware('can:super_admin')->name('rental-management.reorder');

	// User Management - Admin only
	Route::group(['middleware' => ['can:super_admin']], function () {


		// Twilio Chat
		Route::get('/twilio/chat', [TwilioChatController::class, 'index'])->name('twilio.chat');
		Route::get('/twilio/chat/messages', [TwilioChatController::class, 'messages'])->name('twilio.chat.messages');
		Route::post('/twilio/chat/send', [TwilioChatController::class, 'send'])->name('twilio.chat.send');
		Route::post('/twilio/chat/upload', [TwilioChatController::class, 'upload'])->name('twilio.chat.upload');
		// media route should be PUBLIC (outside admin); defined below


		Route::resource('booking-settings', \App\Http\Controllers\BookingSettingsController::class)->except(['show']);
		Route::post('booking-settings/reorder', [\App\Http\Controllers\BookingSettingsController::class, 'reorder'])->name('booking-settings.reorder');
		Route::post('booking-settings/save-email-content', [\App\Http\Controllers\BookingSettingsController::class, 'saveEmailContent'])->name('booking-settings.save-email-content');
		Route::post('booking-settings/save-chat-initial', [\App\Http\Controllers\BookingSettingsController::class, 'saveChatInitialMessage'])->name('booking-settings.save-chat-initial');
		Route::post('booking-settings/save-sms-templates', [\App\Http\Controllers\BookingSettingsController::class, 'saveSmsTemplates'])->name('booking-settings.save-sms-templates');
		Route::post('booking-settings/save-pre-end-reminders', [\App\Http\Controllers\BookingSettingsController::class, 'savePreEndReminders'])->name('booking-settings.save-pre-end-reminders');
		Route::post('booking-settings/save-end-day-morning', [\App\Http\Controllers\BookingSettingsController::class, 'saveEndDayMorning'])->name('booking-settings.save-end-day-morning');
		Route::post('booking-settings/save-notifications', [\App\Http\Controllers\BookingSettingsController::class, 'saveNotifications'])->name('booking-settings.save-notifications');
		Route::get('twilio/logs', [\App\Http\Controllers\TwilioLogsController::class, 'index'])->name('twilio.logs');
		Route::get('email/logs', [\App\Http\Controllers\EmailLogsController::class, 'index'])->name('email.logs');
		Route::resource('user-management', UserController::class);
		Route::resource('role-management', RoleController::class);
		Route::resource('item-management', ItemController::class);
		Route::post('item-management/reorder', [ItemController::class, function (\Illuminate\Http\Request $request) {
			$validated = $request->validate(['orders' => 'required|array', 'orders.*.id' => 'required|integer|exists:items,id', 'orders.*.sort_order' => 'required|integer|min:0']);
			DB::transaction(function () use ($validated) {
				foreach ($validated['orders'] as $o) {
					\App\Models\Item::where('id', $o['id'])->update(['sort_order' => (int)$o['sort_order']]);
				}
			});
			return response()->json(['success' => true]);
		}])->name('item-management.reorder');
		Route::resource('bundle-management', BundleController::class);
		// Place specific coupon routes BEFORE the resource route to avoid conflicts with `coupon-management/{id}`
		Route::get('/coupon-management/logs', [CouponManagementController::class, 'logs'])->name('coupon-management.logs');
		Route::get('/coupon-management/send-status/{id}', [CouponManagementController::class, 'sendStatus'])->name('coupon-management.send-status');
		Route::post('/coupon-management/{id}/send', [CouponManagementController::class, 'send'])->name('coupon-management.send');
		Route::get('/coupon-management/{id}/preview', [CouponManagementController::class, 'preview'])->name('coupon-management.preview');
		Route::resource('coupon-management', CouponManagementController::class);
		Route::resource('faq-management', FaqManagementController::class);
		Route::post('faq-management/reorder', [FaqManagementController::class, function (\Illuminate\Http\Request $request) {
			$validated = $request->validate(['orders' => 'required|array', 'orders.*.id' => 'required|integer|exists:faqs,id', 'orders.*.sort_order' => 'required|integer|min:0']);
			DB::transaction(function () use ($validated) {
				foreach ($validated['orders'] as $o) {
					\App\Models\Faq::where('id', $o['id'])->update(['sort_order' => (int)$o['sort_order']]);
				}
			});
			return response()->json(['success' => true]);
		}])->name('faq-management.reorder');
		Route::resource('privacy-policy-management', PrivacyPolicyManagementController::class);
		Route::resource('terms-condition-management', TermsConditionManagementController::class);
		// Allow only super_admin to delete rentals
		Route::delete('/rental-management/{id}', [RentalManagementController::class, 'destroy'])->name('rental-management.destroy');
	});
});
