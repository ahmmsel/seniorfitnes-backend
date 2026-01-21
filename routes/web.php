<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Redirect root to admin panel
Route::get('/', function () {
    return redirect('/admin');
});

// OAuth Routes (Web-based flow for browser authentication)
Route::prefix('auth')->group(function () {
    // Google OAuth
    Route::get('google/redirect', [AuthController::class, 'googleRedirect'])->name('auth.google.redirect');
    Route::get('google/callback', [AuthController::class, 'googleCallback'])->name('auth.google.callback');
});
