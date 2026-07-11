<?php

use App\Http\Controllers\SettlementArchiveController;
use App\Http\Controllers\SettlementController;
use Illuminate\Support\Facades\Route;

Route::prefix('settlements')->name('settlements.')->group(function () {
    Route::get('/', [SettlementController::class, 'index'])->name('index');
    Route::get('/history', [SettlementController::class, 'history'])->name('history');
    
    // Ledger and Forms (Phase 1 Scaffold)
    Route::get('/contact/{contact}', [SettlementController::class, 'showContact'])->name('contact.show');
    Route::get('/contact/{contact}/create', [SettlementController::class, 'create'])->name('create');
    Route::post('/contact/{contact}', [SettlementController::class, 'store'])->name('store');
    
    Route::get('/{settlement}/edit', [SettlementController::class, 'edit'])->name('edit');
    Route::put('/{settlement}', [SettlementController::class, 'update'])->name('update');
    Route::delete('/{settlement}', [SettlementController::class, 'destroy'])->name('destroy');
    
    // Archive routes
    Route::post('/archive', [SettlementArchiveController::class, 'store'])->name('archive');
    Route::post('/unarchive', [SettlementArchiveController::class, 'destroy'])->name('unarchive');
});
