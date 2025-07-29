<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BundleController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\RoleController;
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
    Route::get('/home', fn() => view('index'))->name('home');
    // Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    // Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    // Route::get('{any}', [RoutingController::class, 'root'])->name('any');

    // User Management
    Route::group(['middleware' => ['can:super_admin']], function () {
        Route::resource('user-management', UserController::class);
        Route::resource('role-management', RoleController::class);
        Route::resource('sport-management', SportController::class);
        Route::resource('tournament-management', TournamentController::class);
        Route::resource('item-management', ItemController::class);
        Route::resource('bundle-management', BundleController::class);
    });
});
