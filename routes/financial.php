<?php

use App\Http\Controllers\FinancialAccountController;
use App\Http\Controllers\FinancialCreditCardController;
use App\Http\Controllers\FinancialCreditCardInvoiceController;
use App\Http\Controllers\FinancialTagController;
use App\Http\Controllers\FinancialTransactionController;
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

    // Cards
    Route::get('/cards/trashed', [FinancialCreditCardController::class, 'trashed'])->name('cards.trashed');
    Route::patch('/cards/{card}/restore', [FinancialCreditCardController::class, 'restore'])->name('cards.restore')->withTrashed();
    Route::delete('/cards/{card}/force', [FinancialCreditCardController::class, 'forceDestroy'])->name('cards.forceDestroy')->withTrashed();
    Route::resource('cards', FinancialCreditCardController::class);
    Route::get('cards/{card}/invoices/{invoice}', [FinancialCreditCardInvoiceController::class, 'show'])->name('cards.invoices.show');
    Route::put('cards/{card}/invoices/{invoice}', [FinancialCreditCardInvoiceController::class, 'update'])->name('cards.invoices.update');
    Route::post('cards/{card}/invoices/{invoice}/pay', [FinancialCreditCardInvoiceController::class, 'pay'])->name('cards.invoices.pay');

    // Transactions
    Route::get('transactions/transfer/create', [FinancialTransactionController::class, 'createTransfer'])->name('transactions.transfer.create');
    Route::post('transactions/transfer', [FinancialTransactionController::class, 'storeTransfer'])->name('transactions.transfer.store');
    Route::resource('transactions', FinancialTransactionController::class)->parameters([
        'transactions' => 'transaction',
    ]);
});
