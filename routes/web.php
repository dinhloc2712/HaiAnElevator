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
use App\Http\Controllers\Admin\MediaController;

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

// Public file serve (for QR code access — no auth required)
Route::get('files/public/{filename}', [MediaController::class, 'publicServe'])
    ->name('media.public-serve')
    ->where('filename', '.*');

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function() { return redirect()->route('admin.profile.show'); });
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::patch('users/{user}/role', [\App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('users.update_role');

    // Role Management
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);

    // Ship Management
    Route::post('ships/quick-store', [\App\Http\Controllers\Admin\ShipController::class, 'quickStore'])->name('ships.quick-store');
    Route::post('ships/import', [\App\Http\Controllers\Admin\ShipController::class, 'import'])->name('ships.import');
    Route::get('ships/export', [\App\Http\Controllers\Admin\ShipController::class, 'export'])->name('ships.export');
    Route::put('ships/{ship}/update-parameters', [\App\Http\Controllers\Admin\ShipController::class, 'updateParameters'])->name('ships.update-parameters');
    Route::patch('ships/{ship}/update-expiration', [\App\Http\Controllers\Admin\ShipController::class, 'updateExpiration'])->name('ships.update-expiration');
    Route::resource('ships', \App\Http\Controllers\Admin\ShipController::class);

    // Shipyard Management
    Route::resource('shipyards', \App\Http\Controllers\Admin\ShipyardController::class);
    Route::post('shipyards/{shipyard}/upload-file', [\App\Http\Controllers\Admin\ShipyardController::class, 'uploadFile'])->name('shipyards.upload-file');
    Route::delete('shipyards/{shipyard}/delete-file', [\App\Http\Controllers\Admin\ShipyardController::class, 'deleteFile'])->name('shipyards.delete-file');

    // Inspection Process Management
    Route::resource('inspection-processes', \App\Http\Controllers\Admin\InspectionProcessController::class);

    // Inspection Execution
    // Route::resource('inspections', \App\Http\Controllers\Admin\InspectionController::class);
    Route::post('inspections/{inspection}/update-status', [\App\Http\Controllers\Admin\InspectionController::class, 'updateStatus'])->name('inspections.update-status');
    Route::post('inspections/{inspection}/request-approval', [\App\Http\Controllers\Admin\InspectionController::class, 'requestApproval'])->name('inspections.request-approval');

    // Profile Management
    Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'show'])->name('profile.show');
    Route::get('profile/edit', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');

    // CRM Management
    Route::get('crm', [\App\Http\Controllers\Admin\CrmController::class, 'index'])->name('crm.index');
    Route::get('crm/ships/{phone?}', [\App\Http\Controllers\Admin\CrmController::class, 'getCustomerShips'])->name('crm.ships');

    // Secure Media Manager
    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::post('media', [MediaController::class, 'store'])->name('media.store');
    Route::post('media/map', [MediaController::class, 'mapFile'])->name('media.map');
    Route::post('media/bulk-delete', [MediaController::class, 'bulkDestroy'])->name('media.bulk-delete');
    Route::post('media/move', [MediaController::class, 'moveItems'])->name('media.move');
    Route::post('media/copy', [MediaController::class, 'copyItems'])->name('media.copy');
    Route::delete('media/{filename}', [MediaController::class, 'destroy'])->name('media.destroy')->where('filename', '.*');
    Route::get('media/file/{filename}', [MediaController::class, 'serve'])->name('media.serve')->where('filename', '.*');
    Route::get('media/{filename}/generate', [MediaController::class, 'showGenerateForm'])->name('media.generate.form')->where('filename', '.*');
    Route::post('media/{filename}/generate', [MediaController::class, 'generateDocument'])->name('media.generate')->where('filename', '.*');
    Route::post('media/{filename}/save-config', [MediaController::class, 'saveConfig'])->name('media.save-config')->where('filename', '.*');
    Route::post('media/folder', [MediaController::class, 'createFolder'])->name('media.create-folder');

    // Finance Management
    Route::resource('finance', \App\Http\Controllers\Admin\FinanceController::class)->except(['show']);

    // KPI Nhân viên
    Route::get('kpi', [\App\Http\Controllers\Admin\KpiController::class, 'index'])->name('kpi.index');
    Route::get('kpi/user/{user}', [\App\Http\Controllers\Admin\KpiController::class, 'userDetail'])->name('kpi.user');
    Route::put('kpi/{user}/commission', [\App\Http\Controllers\Admin\KpiController::class, 'updateCommission'])->name('kpi.commission.update');
    Route::put('kpi/{user}/ships', [\App\Http\Controllers\Admin\KpiController::class, 'updateAssignedShips'])->name('kpi.ships.update');
    Route::post('kpi/reset', [\App\Http\Controllers\Admin\KpiController::class, 'resetKpi'])->name('kpi.reset');

    // News/Announcements Management
    Route::resource('news', \App\Http\Controllers\Admin\NewsController::class);
    Route::get('news/{news}/download', [\App\Http\Controllers\Admin\NewsController::class, 'downloadAttachment'])->name('news.download');

    // Proposals & Approvals
    Route::resource('proposals', \App\Http\Controllers\Admin\ProposalController::class)->only(['index', 'store', 'destroy']);
    Route::put('proposals/{proposal}/status', [\App\Http\Controllers\Admin\ProposalController::class, 'updateStatus'])->name('proposals.update-status');
    Route::put('proposals/{proposal}/steps/{step}/delegate', [\App\Http\Controllers\Admin\ProposalController::class, 'delegateApproval'])->name('proposals.delegate');
    Route::put('proposals/{proposal}/bulk-approve', [\App\Http\Controllers\Admin\ProposalController::class, 'bulkApprove'])->name('proposals.bulk-approve');
    Route::post('proposals/steps/{step}/upload-file', [\App\Http\Controllers\Admin\ProposalController::class, 'uploadFile'])->name('proposals.upload-file');
    Route::delete('proposals/steps/{step}/delete-file', [\App\Http\Controllers\Admin\ProposalController::class, 'deleteFile'])->name('proposals.delete-file');

    // Viettel MySign Gateway Routes
    Route::post('proposals/{proposal}/mysign-sign', [\App\Http\Controllers\Admin\ProposalController::class, 'mySignDocument'])->name('proposals.mysign.sign');
    Route::get('proposals/{proposal}/mysign-poll', [\App\Http\Controllers\Admin\ProposalController::class, 'mySignPoll'])->name('proposals.mysign.poll');
    Route::post('proposals/{proposal}/embed-qr', [\App\Http\Controllers\Admin\ProposalController::class, 'embedQr'])->name('proposals.embed-qr');

    // Proposal Templates
    Route::get('proposals/templates/search', [\App\Http\Controllers\Admin\ProposalTemplateController::class, 'getTemplates'])->name('proposals.templates.search');
    Route::post('proposals/templates/fill', [\App\Http\Controllers\Admin\ProposalTemplateController::class, 'fillTemplate'])->name('proposals.templates.fill');
});
