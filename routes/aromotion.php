<?php

use App\Http\Controllers\AroMotionController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::redirect('/aromotion', '/solutions/aromotion', 301);

Route::prefix('solutions/aromotion')->name('aromotion.')->group(function (): void {
    Route::get('/', [AroMotionController::class, 'show'])->name('show');
    Route::get('/account', [AroMotionController::class, 'auth'])->name('account');

    Route::middleware('guest')->group(function (): void {
        Route::post('/register', [AroMotionController::class, 'register'])
            ->middleware('throttle:8,1')
            ->name('register');
        Route::post('/login', [AroMotionController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('login');
    });

    Route::middleware('auth')->group(function (): void {
        Route::get('/dashboard', [AroMotionController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [AroMotionController::class, 'logout'])->name('logout');
        Route::get('/download/windows', [AroMotionController::class, 'download'])
            ->middleware('throttle:20,1')
            ->name('download.windows');
    });

    Route::get('/manifest.json', [AroMotionController::class, 'manifest'])
        ->middleware('throttle:120,1')
        ->name('manifest');
});

Route::prefix('api/aromotion/v1')->name('aromotion.api.')
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->group(function (): void {
        Route::post('/activate', [AroMotionController::class, 'activate'])
            ->middleware('throttle:20,1')
            ->name('activate');
        Route::post('/heartbeat', [AroMotionController::class, 'heartbeat'])
            ->middleware('throttle:120,1')
            ->name('heartbeat');
        Route::post('/projects/sync', [AroMotionController::class, 'syncProject'])
            ->middleware('throttle:120,1')
            ->name('projects.sync');
    });
