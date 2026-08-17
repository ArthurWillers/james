<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactGroupController;
use Illuminate\Support\Facades\Route;

Route::prefix('contacts')->name('contacts.')->group(function () {
    Route::get('/', [ContactController::class, 'index'])->name('index');
    Route::get('/trashed', [ContactController::class, 'trashed'])->name('trashed');
    Route::get('/create', [ContactController::class, 'create'])->name('create');
    Route::post('/', [ContactController::class, 'store'])->name('store');

    // Groups
    Route::resource('groups', ContactGroupController::class);
    Route::get('/{contact}', [ContactController::class, 'show'])->name('show');
    Route::get('/{contact}/edit', [ContactController::class, 'edit'])->name('edit');
    Route::put('/{contact}', [ContactController::class, 'update'])->name('update');
    Route::post('/{contact}/groups/sync', [ContactController::class, 'syncGroups'])->name('groups.sync');
    Route::delete('/{contact}', [ContactController::class, 'destroy'])->name('destroy');
    Route::patch('/{contact}/restore', [ContactController::class, 'restore'])->name('restore')->withTrashed();
    Route::delete('/{contact}/force', [ContactController::class, 'forceDestroy'])->name('force')->withTrashed();
    Route::get('/{contact}/avatar', [ContactController::class, 'avatar'])->name('avatar')->withTrashed();
    Route::delete('/{contact}/avatar', [ContactController::class, 'destroyAvatar'])->name('destroy-avatar');
});
