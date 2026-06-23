<?php

use App\Http\Controllers\FinancialTagController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/settings', 'settings')->name('settings');

    Route::get('/ui/icons/{name}', [FinancialTagController::class, 'fetchIcon'])->name('ui.icons.show');

    require __DIR__.'/contacts.php';
    require __DIR__.'/financial.php';
});
