<?php

use App\Http\Controllers\Auth\AuthenticatedUserController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutAllController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/auth')->name('api.v1.auth.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::post('register', RegisterController::class)
            ->middleware('throttle:registration')
            ->name('register');
        Route::post('login', LoginController::class)
            ->middleware('throttle:login')
            ->name('login');
        Route::post('forgot-password', ForgotPasswordController::class)
            ->middleware('throttle:password-recovery')
            ->name('password.email');
        Route::post('reset-password', ResetPasswordController::class)
            ->middleware('throttle:password-recovery')
            ->name('password.update');
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('me', AuthenticatedUserController::class)->name('me');
        Route::post('logout', LogoutController::class)->name('logout');
        Route::post('logout-all', LogoutAllController::class)->name('logout-all');
        Route::patch('password', ChangePasswordController::class)->name('password.change');
        Route::post('email/verification-notification', EmailVerificationNotificationController::class)
            ->middleware('throttle:verification')
            ->name('verification.send');
    });
});

Route::get('api/v1/auth/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth:sanctum', 'signed', 'throttle:verification'])
    ->name('verification.verify');

Route::get('/reset-password/{token}', function (Request $request, string $token) {
    return redirect()->away(rtrim((string) config('app.frontend_url'), '/').'/reset-password?'.http_build_query([
        'token' => $token,
        'email' => $request->query('email'),
    ]));
})->middleware('guest')->name('password.reset');

Route::get('/', function () {
    return view('welcome');
});
