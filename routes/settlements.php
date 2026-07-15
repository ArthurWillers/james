<?php

use App\Http\Controllers\SettlementArchiveController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\SettlementGroupController;
use Illuminate\Support\Facades\Route;

Route::prefix('settlements')->name('settlements.')->group(function () {
    Route::get('/', [SettlementController::class, 'index'])->name('index');
    Route::get('/history', [SettlementController::class, 'history'])->name('history');

    // Split Bill (Divisão de Contas)
    Route::get('/groups/trashed', [SettlementGroupController::class, 'trashed'])->name('groups.trashed');
    Route::post('/groups/{id}/restore', [SettlementGroupController::class, 'restore'])->name('groups.restore');
    Route::delete('/groups/{id}/force-delete', [SettlementGroupController::class, 'forceDelete'])->name('groups.force-delete');

    Route::get('/groups', [SettlementGroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/create', [SettlementGroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [SettlementGroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{settlementGroup}', [SettlementGroupController::class, 'show'])->name('groups.show');
    Route::get('/groups/{settlementGroup}/edit', [SettlementGroupController::class, 'edit'])->name('groups.edit');
    Route::put('/groups/{settlementGroup}', [SettlementGroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{settlementGroup}', [SettlementGroupController::class, 'destroy'])->name('groups.destroy');

    // Individual Settlements Trashed
    Route::get('/trashed', [SettlementController::class, 'trashed'])->name('trashed');
    Route::post('/{id}/restore', [SettlementController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [SettlementController::class, 'forceDelete'])->name('force-delete');

    // Ledger and Forms (Phase 1 Scaffold)
    Route::get('/contact/{contact}', [SettlementController::class, 'showContact'])->name('contact.show');
    Route::get('/contact/{contact}/create', [SettlementController::class, 'create'])->name('create');
    Route::post('/contact/{contact}', [SettlementController::class, 'store'])->name('store');

    Route::get('/{settlement}', [SettlementController::class, 'show'])->name('show_item');
    Route::get('/{settlement}/edit', [SettlementController::class, 'edit'])->name('edit');
    Route::put('/{settlement}', [SettlementController::class, 'update'])->name('update');
    Route::delete('/{settlement}', [SettlementController::class, 'destroy'])->name('destroy');

    // Archive routes
    Route::post('/archive', [SettlementArchiveController::class, 'store'])->name('archive');
    Route::post('/unarchive', [SettlementArchiveController::class, 'destroy'])->name('unarchive');
});
