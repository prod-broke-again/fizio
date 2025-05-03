<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TelegramAuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\API\ProgressController;
use App\Http\Controllers\API\WorkoutController;
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
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::post('/fitness-goal', [UserController::class, 'saveFitnessGoal']);
        Route::get('/fitness-goal', [UserController::class, 'getFitnessGoal']);
        Route::get('/gender', [UserController::class, 'getGender']);
        Route::post('/profile', [UserController::class, 'updateProfile']);
    });
    
    // Прогресс и статистика
    Route::prefix('progress')->group(function () {
        Route::get('/daily', [ProgressController::class, 'dailyProgress']);
        Route::post('/update', [ProgressController::class, 'updateProgress']);
    });
    
    // Тренировки
    Route::prefix('workouts')->group(function () {
        Route::get('/schedule', [WorkoutController::class, 'getSchedule']);
        Route::post('/', [WorkoutController::class, 'store']);
        Route::put('/{id}', [WorkoutController::class, 'update']);
        Route::delete('/{id}', [WorkoutController::class, 'destroy']);
    });
    
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
});

// Маршруты для Telegram webhook
Route::prefix('telegram')->group(function () {
    Route::post('/webhook', [TelegramController::class, 'handleWebhook']);
    Route::get('/setup-webhook', [TelegramController::class, 'setupWebhook']);
    Route::get('/webhook-info', [TelegramController::class, 'getWebhookInfo']);
}); 

// Чат с AI-ассистентом
Route::prefix('chat')->middleware('auth:sanctum')->group(function () {
    Route::post('/send', [App\Http\Controllers\API\ChatController::class, 'sendMessage']);
    Route::get('/history', [App\Http\Controllers\API\ChatController::class, 'getChatHistory']);
    Route::post('/voice', [App\Http\Controllers\API\ChatController::class, 'processVoice']);
}); 

// Тестовый маршрут для проверки API GPTunnel
Route::get('/test-gptunnel', [App\Http\Controllers\API\ChatController::class, 'testGptunnel']); 