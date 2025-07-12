<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TelegramAuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\API\WorkoutController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FatSecretController;
use App\Http\Controllers\OpenFoodFactsController;
use App\Http\Controllers\SpoonacularController;
use App\Http\Controllers\API\NutritionController;
use App\Http\Controllers\API\AppleWatchController;
use App\Http\Controllers\API\HealthKitController;
use App\Http\Controllers\WeekPlanController;
use App\Http\Controllers\API\ProgressController;
use App\Http\Controllers\API\ReadyMealController;

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
    Route::prefix('progress')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [ProgressController::class, 'getOverallProgress']);
        Route::get('/by-date', [ProgressController::class, 'getProgressByDate']);
        Route::post('/measurements', [ProgressController::class, 'storeOrUpdateProgress']);
        Route::patch('/update', [ProgressController::class, 'storeOrUpdateProgress']);

        Route::post('/goals', [ProgressController::class, 'addGoal']);
        Route::post('/goals/{goalId}/progress', [ProgressController::class, 'updateGoalProgress']);

        Route::get('/statistics', [ProgressController::class, 'getStatistics']);
        Route::get('/achievements', [ProgressController::class, 'getAchievements']);
    });
    
    // Тренировки
    Route::prefix('workouts')->middleware('auth:sanctum')->group(function () {
        Route::get('plan', [WorkoutController::class, 'getWorkoutPlan']);
        Route::get('/', [WorkoutController::class, 'index']);
        Route::post('/', [WorkoutController::class, 'addWorkout']);
        Route::get('/{workoutId}', [WorkoutController::class, 'show']);
        Route::put('/{workoutId}', [WorkoutController::class, 'updateWorkout']);
        Route::delete('/{workoutId}', [WorkoutController::class, 'destroy']);
        Route::patch('/{workoutId}/complete', [WorkoutController::class, 'markWorkoutAsCompleted']);
    });
    
    Route::get('/notifications', [\App\Http\Controllers\API\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\API\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\API\NotificationController::class, 'markAllAsRead']);
    
    // Питание
    Route::prefix('nutrition')->middleware('auth:sanctum')->group(function () {
        Route::get('plan', [NutritionController::class, 'getNutritionPlan']);
        Route::get('daily', [NutritionController::class, 'getDailyNutrition']);
        Route::post('meals', [NutritionController::class, 'addMeal']);
        Route::put('meals/{mealId}', [NutritionController::class, 'updateMeal']);
        Route::delete('meals/{mealId}', [NutritionController::class, 'deleteMeal']);
        Route::patch('meals/{mealId}/complete', [NutritionController::class, 'markMealAsCompleted']);
    });
    
    // Apple Watch
    Route::post('/integrations/apple-watch/connect', [AppleWatchController::class, 'connect']);
    Route::post('/integrations/apple-watch/sync', [AppleWatchController::class, 'sync']);

    // HealthKit
    Route::post('/healthkit/sync', [HealthKitController::class, 'sync']);
    
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Week Plan
    Route::get('/week-plan', [WeekPlanController::class, 'index']);
    Route::get('/week-plan/{date}', [WeekPlanController::class, 'show']);
    Route::post('/week-plan/{date}/meal', [WeekPlanController::class, 'addMeal']);
    Route::post('/week-plan/{date}/workout', [WeekPlanController::class, 'addWorkout']);
    Route::patch('/week-plan/{date}/progress', [WeekPlanController::class, 'updateProgress']);
    Route::patch('/week-plan/{date}/{type}/{id}/toggle-complete', [WeekPlanController::class, 'markAsCompleted']);

    // Маршруты для готовых блюд
    Route::get('/ready-meals', [ReadyMealController::class, 'index']);
    Route::post('/ready-meals/add-to-plan', [ReadyMealController::class, 'addToPlan']);
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
    Route::delete('/clear', [App\Http\Controllers\API\ChatController::class, 'clearChatHistory']);
}); 

// Тестовый маршрут для проверки API GPTunnel
Route::get('/test-gptunnel', [App\Http\Controllers\API\ChatController::class, 'testGptunnel']); 

Route::prefix('fatsecret')->group(function () {
    Route::post('token', [FatSecretController::class, 'getToken']);
    Route::get('foods/search', [FatSecretController::class, 'searchFoods']);
    Route::get('foods/autocomplete', [FatSecretController::class, 'autocomplete']);
    Route::post('foods/recognize', [FatSecretController::class, 'recognizeByImage']);
    Route::get('foods/categories', [FatSecretController::class, 'getCategories']);
    Route::get('foods/{foodId}', [FatSecretController::class, 'getFood']);
    Route::get('brands/search', [FatSecretController::class, 'searchBrands']);
    Route::get('recipes/search', [FatSecretController::class, 'searchRecipes']);
}); 

Route::prefix('openfoodfacts')->group(function () {
    Route::get('products/search', [OpenFoodFactsController::class, 'searchProducts']);
    Route::get('products/barcode/{barcode}', [OpenFoodFactsController::class, 'getProductByBarcode']);
}); 

Route::prefix('spoonacular')->group(function () {
    Route::get('products/search', [SpoonacularController::class, 'searchProducts']);
    Route::get('products/upc/{upc}', [SpoonacularController::class, 'getProductByUPC']);
    Route::get('products/{id}/information', [SpoonacularController::class, 'getProductInformation']);
}); 
