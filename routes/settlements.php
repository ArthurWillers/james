<?php

use App\Http\Controllers\SettlementArchiveController;
use App\Http\Controllers\SettlementController;
use Illuminate\Support\Facades\Route;

Route::prefix('acertos')->name('settlements.')->group(function () {
    Route::get('/', [SettlementController::class, 'index'])->name('index');
    
    // Archive routes
    Route::post('/archive', [SettlementArchiveController::class, 'store'])->name('archive');
    Route::post('/unarchive', [SettlementArchiveController::class, 'destroy'])->name('unarchive');
});
