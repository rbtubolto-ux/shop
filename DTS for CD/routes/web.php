<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\RecordsController;
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

    // User guide: incoming and outgoing documents
    Route::view('help/incoming-outgoing', 'help.directions')->name('help.directions');

    // Workflows
    Route::resource('workflows', WorkflowController::class);

    // Archive
    Route::get('archive', [ArchiveController::class, 'index'])->name('archive.index');

    // Logbook (FAM 13.1 section 3.7)
    Route::get('logbook', [LogbookController::class, 'index'])->name('logbook.index');

    // Records requisitions (FAM 13.1 section 3.10)
    Route::get('requisitions', [RequisitionController::class, 'index'])->name('requisitions.index');
    Route::post('documents/{document}/requisitions', [RequisitionController::class, 'store'])->name('requisitions.store');
    Route::patch('requisitions/{requisition}/issue', [RequisitionController::class, 'issue'])->name('requisitions.issue');
    Route::patch('requisitions/{requisition}/acknowledge', [RequisitionController::class, 'acknowledge'])->name('requisitions.acknowledge');
    Route::patch('requisitions/{requisition}/return', [RequisitionController::class, 'returned'])->name('requisitions.return');

    // Records management: inventory and classification (FAM 13.1 sections 3.11-3.15)
    Route::get('records', [RecordsController::class, 'index'])->name('records.index');
    Route::patch('records/{document}/disposition', [RecordsController::class, 'setDisposition'])->name('records.disposition');
    Route::post('records/{document}/examine', [RecordsController::class, 'examine'])->name('records.examine');
    Route::post('records/reviews', [RecordsController::class, 'review'])->name('records.reviews.store');


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
