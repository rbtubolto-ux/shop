<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/login',  [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:5,1']);
Route::post('/logout',[AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Documents
    Route::resource('documents', DocumentController::class);
    Route::patch('documents/{document}/submit',       [DocumentController::class, 'submit'])->name('documents.submit');
    Route::post('documents/{document}/route-action',  [DocumentController::class, 'routeAction'])->name('documents.routeAction');
    Route::patch('documents/{document}/archive',      [DocumentController::class, 'archive'])->name('documents.archive');
    Route::get('documents/{document}/barcode',        [DocumentController::class, 'printBarcode'])->name('documents.barcode');
    Route::get('documents/{document}/file',        [DocumentController::class, 'file'])->name('documents.file');

    // Workflows
    Route::resource('workflows', WorkflowController::class);

    // Archive
    Route::get('archive', [ArchiveController::class, 'index'])->name('archive.index');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // Users
    Route::resource('users', UserController::class)->except(['show']);

    // Notifications
    Route::get('notifications',                [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::get('notifications/unread-count',   [NotificationController::class, 'getUnreadCount'])->name('notifications.unreadCount');

    // Profile
    Route::get('profile',                    [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile',                    [ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/signature',         [ProfileController::class, 'uploadSignature'])->name('profile.signature');
    Route::delete('profile/signature',       [ProfileController::class, 'removeSignature'])->name('profile.signature.remove');
});
