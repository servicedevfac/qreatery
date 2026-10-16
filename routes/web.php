<?php

use App\Http\Controllers\Auth\AuthenticatedUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/auth')->name('api.v1.auth.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::post('register', RegisterController::class)
            ->middleware('throttle:registration')
            ->name('register');
        Route::post('login', LoginController::class)
            ->middleware('throttle:login')
            ->name('login');
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('me', AuthenticatedUserController::class)->name('me');
        Route::post('logout', LogoutController::class)->name('logout');
    });
});

Route::get('/', function () {
    return view('welcome');
});
