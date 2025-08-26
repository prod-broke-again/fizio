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
    
    // Управление приёмами пищи и их элементами
    Route::prefix('meals')->middleware('auth:sanctum')->group(function () {
        Route::post('/', [\App\Http\Controllers\API\MealController::class, 'store']);
        Route::get('/{meal}', [\App\Http\Controllers\API\MealController::class, 'show']);
        Route::put('/{meal}', [\App\Http\Controllers\API\MealController::class, 'update']);
        Route::delete('/{meal}', [\App\Http\Controllers\API\MealController::class, 'destroy']);
        
        // Элементы приёма пищи
        Route::post('/{meal}/items', [\App\Http\Controllers\API\MealItemController::class, 'store']);
        Route::patch('/{meal}/items/{mealItem}', [\App\Http\Controllers\API\MealItemController::class, 'update']);
        Route::delete('/{meal}/items/{mealItem}', [\App\Http\Controllers\API\MealItemController::class, 'destroy']);
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

// 1) Поиск локальных продуктов (алиас к существующему локальному поиску)
Route::prefix('products')->middleware('auth:sanctum')->group(function () {
    Route::get('search', [\App\Http\Controllers\API\ProductSearchController::class, 'searchLocal']);
    Route::get('{id}',   [\App\Http\Controllers\API\ProductSearchController::class, 'showLocal']);
});

// 2) Превью и применение питания
Route::prefix('meals')->middleware('auth:sanctum')->group(function () {
    Route::post('preview', [\App\Http\Controllers\API\MealsApplyController::class, 'preview']);
    Route::post('apply',   [\App\Http\Controllers\API\MealsApplyController::class, 'apply']);
    Route::post('{meal}/items/bulk', [\App\Http\Controllers\API\MealItemController::class, 'bulkStore']);
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

// API V2 - Система тренировок с подпиской
Route::prefix('v2')->group(function () {
    // Открытые маршруты для категорий и программ
    Route::prefix('workout-categories')->group(function () {
        Route::get('/', [App\Http\Controllers\API\V2\WorkoutCategoryV2Controller::class, 'index']);
        Route::get('/{slug}', [App\Http\Controllers\API\V2\WorkoutCategoryV2Controller::class, 'show']);
    });
    
    Route::prefix('workout-programs')->group(function () {
        Route::get('/', [App\Http\Controllers\API\V2\WorkoutProgramV2Controller::class, 'index']);
        Route::get('/{slug}', [App\Http\Controllers\API\V2\WorkoutProgramV2Controller::class, 'show']);
        Route::get('/category/{categorySlug}', [App\Http\Controllers\API\V2\WorkoutProgramV2Controller::class, 'getByCategory']);
    });
    
    Route::prefix('workout-exercises')->group(function () {
        Route::get('/{id}', [App\Http\Controllers\API\V2\WorkoutExerciseV2Controller::class, 'show']);
        Route::get('/program/{programId}', [App\Http\Controllers\API\V2\WorkoutExerciseV2Controller::class, 'getByProgram']);
    });
    
    // Защищенные маршруты (требуется авторизация)
    Route::middleware('auth:sanctum')->group(function () {
        // Прогресс пользователя
        Route::prefix('user/workout-progress')->group(function () {
            Route::get('/', [App\Http\Controllers\API\V2\UserWorkoutProgressV2Controller::class, 'index']);
            Route::post('/', [App\Http\Controllers\API\V2\UserWorkoutProgressV2Controller::class, 'store']);
            Route::put('/{id}', [App\Http\Controllers\API\V2\UserWorkoutProgressV2Controller::class, 'update']);
            Route::get('/statistics', [App\Http\Controllers\API\V2\UserWorkoutProgressV2Controller::class, 'statistics']);
        });
        
        // Подписки пользователя
        Route::prefix('user/subscription')->group(function () {
            Route::get('/', [App\Http\Controllers\API\V2\UserSubscriptionV2Controller::class, 'show']);
            Route::post('/', [App\Http\Controllers\API\V2\UserSubscriptionV2Controller::class, 'store']);
            Route::delete('/cancel', [App\Http\Controllers\API\V2\UserSubscriptionV2Controller::class, 'cancel']);
            Route::get('/status', [App\Http\Controllers\API\V2\UserSubscriptionV2Controller::class, 'status']);
        });
    });
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

Route::prefix('local-products')->group(function () {
    Route::get('search', [App\Http\Controllers\LocalProductsController::class, 'searchProducts']);
    Route::get('barcode/{barcode}', [App\Http\Controllers\LocalProductsController::class, 'getProductByBarcode']);
    Route::get('cis', [App\Http\Controllers\LocalProductsController::class, 'getCISProducts']);
    Route::get('russian', [App\Http\Controllers\LocalProductsController::class, 'getRussianProducts']);
    Route::get('stats', [App\Http\Controllers\LocalProductsController::class, 'getStats']);
}); 

Route::prefix('spoonacular')->group(function () {
    // Продукты
    Route::get('products/search', [SpoonacularController::class, 'searchProducts']);
    Route::get('products/upc/{upc}', [SpoonacularController::class, 'getProductByUPC']);
    Route::get('products/{id}/information', [SpoonacularController::class, 'getProductInformation']);
    
    // Рецепты
    Route::get('recipes/search', [SpoonacularController::class, 'searchRecipes']);
    Route::get('recipes/by-ingredients', [SpoonacularController::class, 'searchRecipesByIngredients']);
    Route::get('recipes/{recipeId}/information', [SpoonacularController::class, 'getRecipeInformation']);
    Route::get('recipes/random', [SpoonacularController::class, 'getRandomRecipes']);
    
    // Ингредиенты
    Route::get('ingredients/search', [SpoonacularController::class, 'searchIngredients']);
    Route::get('ingredients/{ingredientId}/information', [SpoonacularController::class, 'getIngredientInformation']);
    Route::get('ingredients/autocomplete', [SpoonacularController::class, 'autocompleteIngredientSearch']);
    
    // Планирование питания
    Route::get('meal-planner/generate', [SpoonacularController::class, 'generateMealPlan']);
    Route::get('meal-planner/week', [SpoonacularController::class, 'getMealPlanWeek']);
    
    // Анализ питания
    Route::post('recipes/analyze', [SpoonacularController::class, 'analyzeRecipe']);
    Route::get('recipes/guess-nutrition', [SpoonacularController::class, 'guessNutritionByDishName']);
    Route::post('recipes/classify-cuisine', [SpoonacularController::class, 'classifyCuisine']);
}); 
