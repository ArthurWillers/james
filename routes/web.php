<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\FinancialTagController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/settings', 'settings')->name('settings');

    Route::get('/ui/icons/{name}', [FinancialTagController::class, 'fetchIcon'])->name('ui.icons.show');
    Route::get('/attachments/{media}/{filename?}', [AttachmentController::class, 'download'])->name('attachments.download');

    Route::get('/audit', [\App\Http\Controllers\AuditController::class, 'index'])->name('audit.index');
    Route::get('/audit/{activity}', [\App\Http\Controllers\AuditController::class, 'show'])->name('audit.show');

    require __DIR__.'/contacts.php';
    require __DIR__.'/financial.php';
    require __DIR__.'/settlements.php';
});
