<?php

use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/settings', 'settings')->name('settings');

    Route::controller(ContactController::class)->prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/trashed', 'trashed')->name('trashed');
        Route::post('/{contact}/restore', 'restore')->name('restore')->withTrashed();
        Route::delete('/{contact}/force-delete', 'forceDestroy')->name('force-destroy')->withTrashed();
        Route::delete('/{contact}/avatar', 'destroyAvatar')->name('destroy-avatar');
    });
    Route::resource('contacts', ContactController::class);
});
