<?php

use App\Http\Controllers\Api\ConsentController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/privacy', [PrivacyController::class, 'index']);
    Route::put('/privacy', [PrivacyController::class, 'update']);
    Route::get('/settings', [UserSettingsController::class, 'index']);
    Route::put('/settings', [UserSettingsController::class, 'update']);
});

Route::get('/consents', [ConsentController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
