<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TelegramAuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Route;

// Открытые маршруты для авторизации
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/telegram', [TelegramAuthController::class, 'auth']);
    Route::post('/telegram/link', [TelegramAuthController::class, 'link']);
});

// Защищенные маршруты (требуется токен)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::post('/fitness-goal', [UserController::class, 'saveFitnessGoal']);
        Route::get('/fitness-goal', [UserController::class, 'getFitnessGoal']);
    });
});

// Маршруты для Telegram webhook
Route::prefix('telegram')->group(function () {
    Route::post('/webhook', [TelegramController::class, 'handleWebhook']);
    Route::get('/setup-webhook', [TelegramController::class, 'setupWebhook']);
    Route::get('/webhook-info', [TelegramController::class, 'getWebhookInfo']);
}); 