<?php

use App\Http\Controllers\FinancialAccountController;
use App\Http\Controllers\FinancialTagController;
use Illuminate\Support\Facades\Route;

Route::prefix('financial')->name('financial.')->group(function () {
    Route::view('/dashboard', 'finance.dashboard')->name('dashboard');

    // Accounts
    Route::get('/accounts/trashed', [FinancialAccountController::class, 'trashed'])->name('accounts.trashed');
    Route::patch('/accounts/{financialAccount}/restore', [FinancialAccountController::class, 'restore'])->name('accounts.restore')->withTrashed();
    Route::delete('/accounts/{financialAccount}/force', [FinancialAccountController::class, 'forceDestroy'])->name('accounts.forceDestroy')->withTrashed();
    Route::resource('accounts', FinancialAccountController::class)->parameters([
        'accounts' => 'financialAccount',
    ]);

    // Tags
    Route::resource('tags', FinancialTagController::class)->parameters([
        'tags' => 'financialTag',
    ]);
});
