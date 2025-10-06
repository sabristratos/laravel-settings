<?php

use Illuminate\Support\Facades\Route;
use Stratos\Settings\Http\Controllers\Api\SettingsController;

Route::prefix(config('settings.api.prefix', 'api/settings'))
    ->middleware(config('settings.api.middleware', ['api']))
    ->name('settings.api.')
    ->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/{key}', [SettingsController::class, 'show'])->name('show');
        Route::post('/', [SettingsController::class, 'store'])->name('store');
        Route::put('/{key}', [SettingsController::class, 'update'])->name('update');
        Route::delete('/{key}', [SettingsController::class, 'destroy'])->name('destroy');
    });
