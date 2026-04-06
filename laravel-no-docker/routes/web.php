<?php

use App\Http\Controllers\LegacyRedirectController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'transition')->name('transition.home');

Route::get('/legacy', [LegacyRedirectController::class, 'home'])->name('legacy.home');
Route::get('/legacy/login', [LegacyRedirectController::class, 'login'])->name('legacy.login');
Route::get('/legacy/admin/{path?}', [LegacyRedirectController::class, 'admin'])
    ->where('path', '.*')
    ->name('legacy.admin');
