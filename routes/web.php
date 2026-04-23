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
use App\Http\Controllers\Admin\PushSubscriptionController;
use App\Http\Controllers\Admin\NotificationController;

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


    Route::post('buildings/import', [\App\Http\Controllers\Admin\BuildingController::class, 'import'])->name('buildings.import');
    Route::resource('buildings', \App\Http\Controllers\Admin\BuildingController::class);

    Route::resource('elevators', \App\Http\Controllers\Admin\ElevatorController::class);
    Route::resource('installations', \App\Http\Controllers\Admin\InstallationController::class);
    Route::post('installations/{installation}/start', [\App\Http\Controllers\Admin\InstallationController::class, 'start'])->name('installations.start');
    Route::post('installations/{installation}/complete', [\App\Http\Controllers\Admin\InstallationController::class, 'complete'])->name('installations.complete');


    Route::get('maintenance/orders', [\App\Http\Controllers\Admin\MaintenanceController::class, 'orders'])->name('maintenance.orders');
    Route::post('maintenance/orders', [\App\Http\Controllers\Admin\MaintenanceController::class, 'storeOrder'])->name('maintenance.orders.store');
    Route::get('maintenance/orders/{order}/edit', [\App\Http\Controllers\Admin\MaintenanceController::class, 'editOrder'])->name('maintenance.orders.edit');
    Route::put('maintenance/orders/{order}', [\App\Http\Controllers\Admin\MaintenanceController::class, 'updateOrder'])->name('maintenance.orders.update');
    Route::get('maintenance/{maintenance}/export', [\App\Http\Controllers\Admin\MaintenanceController::class, 'export'])->name('maintenance.export');
    Route::resource('maintenance', \App\Http\Controllers\Admin\MaintenanceController::class);
    Route::resource('incidents', \App\Http\Controllers\Admin\IncidentController::class);
    
    // Maintenance Settings
    Route::get('maintenance-settings', [\App\Http\Controllers\Admin\MaintenanceSettingController::class, 'index'])->name('maintenance.settings');
    Route::post('maintenance-settings/categories', [\App\Http\Controllers\Admin\MaintenanceSettingController::class, 'storeCategory'])->name('maintenance.categories.store');
    Route::put('maintenance-settings/categories/{category}', [\App\Http\Controllers\Admin\MaintenanceSettingController::class, 'updateCategory'])->name('maintenance.categories.update');
    Route::delete('maintenance-settings/categories/{category}', [\App\Http\Controllers\Admin\MaintenanceSettingController::class, 'destroyCategory'])->name('maintenance.categories.destroy');
    
    Route::post('maintenance-settings/items', [\App\Http\Controllers\Admin\MaintenanceSettingController::class, 'storeItem'])->name('maintenance.items.store');
    Route::put('maintenance-settings/items/{item}', [\App\Http\Controllers\Admin\MaintenanceSettingController::class, 'updateItem'])->name('maintenance.items.update');
    Route::delete('maintenance-settings/items/{item}', [\App\Http\Controllers\Admin\MaintenanceSettingController::class, 'destroyItem'])->name('maintenance.items.destroy');
    
    Route::post('maintenance-settings/statuses', [\App\Http\Controllers\Admin\MaintenanceSettingController::class, 'storeStatus'])->name('maintenance.statuses.store');
    Route::put('maintenance-settings/statuses/{status}', [\App\Http\Controllers\Admin\MaintenanceSettingController::class, 'updateStatus'])->name('maintenance.statuses.update');
    Route::delete('maintenance-settings/statuses/{status}', [\App\Http\Controllers\Admin\MaintenanceSettingController::class, 'destroyStatus'])->name('maintenance.statuses.destroy');

    Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');

    // Profile Management
    Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'show'])->name('profile.show');
    Route::get('profile/edit', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');

    // Push Subscriptions
    Route::post('push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push.subscriptions.store');
    Route::delete('push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push.subscriptions.destroy');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
});
