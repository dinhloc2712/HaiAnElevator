<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Universal Fallback Route for Missing Storage Symlink on Shared Hosting (handles all files in storage/app/public)
Route::get('storage/{path}', [\App\Http\Controllers\Admin\UserController::class, 'servePublicStorageFile'])->where('path', '.*')->name('storage.fallback');

// Auth Routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.post');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Forgot Password Routes (Guest)
Route::get('/forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/forgot-password/verify', [AuthController::class, 'showResetVerifyForm'])->name('password.verify');
Route::post('/forgot-password/verify', [AuthController::class, 'verifyResetOtpAndChange'])->name('password.update');

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function() { return redirect()->route('admin.dashboard'); });
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::patch('users/{user}/role', [\App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('users.update_role');

    // Role Management
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);

    // Branch Management
    Route::resource('branches', \App\Http\Controllers\Admin\BranchController::class);


    Route::resource('buildings', \App\Http\Controllers\Admin\BuildingController::class);
    Route::resource('elevators', \App\Http\Controllers\Admin\ElevatorController::class);
    Route::resource('installations', \App\Http\Controllers\Admin\InstallationController::class);
    Route::post('installations/{installation}/complete', [\App\Http\Controllers\Admin\InstallationController::class, 'complete'])->name('installations.complete');

    Route::post('maintenance/schedule', [\App\Http\Controllers\Admin\MaintenanceController::class, 'schedule'])->name('maintenance.schedule');
    Route::resource('maintenance', \App\Http\Controllers\Admin\MaintenanceController::class);

    // Profile Management
    Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'show'])->name('profile.show');
    Route::get('profile/edit', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');

    // News/Announcements Management
    Route::resource('news', \App\Http\Controllers\Admin\NewsController::class);
    Route::get('news/{news}/download', [\App\Http\Controllers\Admin\NewsController::class, 'downloadAttachment'])->name('news.download');
});
