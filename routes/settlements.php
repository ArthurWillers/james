<?php

use Illuminate\Support\Facades\Route;

Route::prefix('settlements')->name('settlements.')->group(function () {
    Route::get('/', function(){
        return "yes";
    })->name('index');
});
