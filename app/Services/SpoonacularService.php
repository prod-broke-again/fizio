<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpoonacularService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.spoonacular.com';

    public function __construct()
    {
        $this->apiKey = config('services.spoonacular.api_key');
    }

    // Существующие методы для продуктов
    public function searchProducts($query, $page = 1, $perPage = 20)
    {
        try {
            $cacheKey = "spoonacular_search_{$query}_{$page}_{$perPage}";
            
            return Cache::remember($cacheKey, 300, function () use ($query, $page, $perPage) {
                $response = Http::get("{$this->baseUrl}/food/products/search", [
                    'apiKey' => $this->apiKey,
                    'query' => $query,
                    'offset' => ($page - 1) * $perPage,
                    'number' => $perPage
                ]);

                if (!$response->successful()) {
                    Log::error('Spoonacular search error', [
                        'query' => $query,
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                    throw new \Exception('Не удалось найти продукты');
                }

                $data = $response->json();
                return [
                    'products' => $data['products'] ?? [],
                    'total' => $data['totalProducts'] ?? 0
                ];
            });
        } catch (\Exception $e) {
            Log::error('Spoonacular search exception', [
                'message' => $e->getMessage(),
                'query' => $query
            ]);
            throw $e;
        }
    }

    public function getProductByUPC($upc)
    {
        try {
            $cacheKey = "spoonacular_upc_{$upc}";
            
            return Cache::remember($cacheKey, 86400, function () use ($upc) {
                $response = Http::get("{$this->baseUrl}/food/products/upc/{$upc}", [
                    'apiKey' => $this->apiKey
                ]);

                if (!$response->successful()) {
                    Log::error('Spoonacular UPC error', [
                        'upc' => $upc,
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                    throw new \Exception('Продукт не найден');
                }

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('Spoonacular UPC exception', [
                'message' => $e->getMessage(),
                'upc' => $upc
            ]);
            throw $e;
        }
    }

    public function getProductInformation(int $productId): ?array
    {
        $cacheKey = "spoonacular_product_info_{$productId}";

        try {
            $response = Http::get("{$this->baseUrl}/food/products/{$productId}", [
                'apiKey' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $productInfo = $response->json();
                Cache::put($cacheKey, $productInfo, now()->addHours(24));
                return $productInfo;
            }

            Log::error('Spoonacular Product Info error', [
                'productId' => $productId,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Spoonacular Product Info exception', [
                'productId' => $productId,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    // Новые методы для рецептов
    public function searchRecipes($query, $options = [])
    {
        try {
            $cacheKey = "spoonacular_recipes_search_" . md5($query . serialize($options));
            
            return Cache::remember($cacheKey, 1800, function () use ($query, $options) {
                $params = array_merge([
                    'apiKey' => $this->apiKey,
                    'query' => $query,
                    'number' => $options['number'] ?? 10,
                    'addRecipeNutrition' => $options['addRecipeNutrition'] ?? true,
                    'instructionsRequired' => $options['instructionsRequired'] ?? true,
                ], $options);

                $response = Http::get("{$this->baseUrl}/recipes/complexSearch", $params);

                if (!$response->successful()) {
                    Log::error('Spoonacular recipes search error', [
                        'query' => $query,
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                    throw new \Exception('Не удалось найти рецепты');
                }

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('Spoonacular recipes search exception', [
                'message' => $e->getMessage(),
                'query' => $query
            ]);
            throw $e;
        }
    }

    public function searchRecipesByIngredients($ingredients, $options = [])
    {
        try {
            $cacheKey = "spoonacular_recipes_ingredients_" . md5(implode(',', $ingredients) . serialize($options));
            
            return Cache::remember($cacheKey, 1800, function () use ($ingredients, $options) {
                $params = array_merge([
                    'apiKey' => $this->apiKey,
                    'ingredients' => implode(',', $ingredients),
                    'number' => $options['number'] ?? 10,
                    'ranking' => $options['ranking'] ?? 1,
                    'ignorePantry' => $options['ignorePantry'] ?? true,
                ], $options);

                $response = Http::get("{$this->baseUrl}/recipes/findByIngredients", $params);

                if (!$response->successful()) {
                    Log::error('Spoonacular recipes by ingredients error', [
                        'ingredients' => $ingredients,
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                    throw new \Exception('Не удалось найти рецепты по ингредиентам');
                }

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('Spoonacular recipes by ingredients exception', [
                'message' => $e->getMessage(),
                'ingredients' => $ingredients
            ]);
            throw $e;
        }
    }

    public function getRecipeInformation($recipeId, $includeNutrition = true)
    {
        try {
            $cacheKey = "spoonacular_recipe_info_{$recipeId}_{$includeNutrition}";
            
            return Cache::remember($cacheKey, 3600, function () use ($recipeId, $includeNutrition) {
                $params = [
                    'apiKey' => $this->apiKey,
                ];

                if ($includeNutrition) {
                    $params['includeNutrition'] = 'true';
                }

                $response = Http::get("{$this->baseUrl}/recipes/{$recipeId}/information", $params);

                if (!$response->successful()) {
                    Log::error('Spoonacular recipe info error', [
                        'recipeId' => $recipeId,
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                    throw new \Exception('Не удалось получить информацию о рецепте');
                }

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('Spoonacular recipe info exception', [
                'message' => $e->getMessage(),
                'recipeId' => $recipeId
            ]);
            throw $e;
        }
    }

    public function getRandomRecipes($options = [])
    {
        try {
            $cacheKey = "spoonacular_random_recipes_" . md5(serialize($options));
            
            return Cache::remember($cacheKey, 1800, function () use ($options) {
                $params = array_merge([
                    'apiKey' => $this->apiKey,
                    'number' => $options['number'] ?? 10,
                    'addRecipeNutrition' => $options['addRecipeNutrition'] ?? true,
                ], $options);

                $response = Http::get("{$this->baseUrl}/recipes/random", $params);

                if (!$response->successful()) {
                    Log::error('Spoonacular random recipes error', [
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                    throw new \Exception('Не удалось получить случайные рецепты');
                }

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('Spoonacular random recipes exception', [
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    // Методы для ингредиентов
    public function searchIngredients($query, $options = [])
    {
        try {
            $cacheKey = "spoonacular_ingredients_search_" . md5($query . serialize($options));
            
            return Cache::remember($cacheKey, 3600, function () use ($query, $options) {
                $params = array_merge([
                    'apiKey' => $this->apiKey,
                    'query' => $query,
                    'number' => $options['number'] ?? 10,
                    'addChildren' => $options['addChildren'] ?? true,
                ], $options);

                $response = Http::get("{$this->baseUrl}/food/ingredients/search", $params);

                if (!$response->successful()) {
                    Log::error('Spoonacular ingredients search error', [
                        'query' => $query,
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                    throw new \Exception('Не удалось найти ингредиенты');
                }

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('Spoonacular ingredients search exception', [
                'message' => $e->getMessage(),
                'query' => $query
            ]);
            throw $e;
        }
    }

    public function getIngredientInformation($ingredientId, $options = [])
    {
        try {
            $cacheKey = "spoonacular_ingredient_info_{$ingredientId}_" . md5(serialize($options));
            
            return Cache::remember($cacheKey, 86400, function () use ($ingredientId, $options) {
                $params = array_merge([
                    'apiKey' => $this->apiKey,
                    'amount' => $options['amount'] ?? 1,
                    'unit' => $options['unit'] ?? 'grams',
                ], $options);

                $response = Http::get("{$this->baseUrl}/food/ingredients/{$ingredientId}/information", $params);

                if (!$response->successful()) {
                    Log::error('Spoonacular ingredient info error', [
                        'ingredientId' => $ingredientId,
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                    throw new \Exception('Не удалось получить информацию об ингредиенте');
                }

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('Spoonacular ingredient info exception', [
                'message' => $e->getMessage(),
                'ingredientId' => $ingredientId
            ]);
            throw $e;
        }
    }

    public function autocompleteIngredientSearch($query, $number = 10)
    {
        try {
            $cacheKey = "spoonacular_ingredient_autocomplete_{$query}_{$number}";
            
            return Cache::remember($cacheKey, 3600, function () use ($query, $number) {
                $response = Http::get("{$this->baseUrl}/food/ingredients/autocomplete", [
                    'apiKey' => $this->apiKey,
                    'query' => $query,
                    'number' => $number,
                ]);

                if (!$response->successful()) {
                    Log::error('Spoonacular ingredient autocomplete error', [
                        'query' => $query,
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                    throw new \Exception('Не удалось выполнить автодополнение ингредиентов');
                }

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('Spoonacular ingredient autocomplete exception', [
                'message' => $e->getMessage(),
                'query' => $query
            ]);
            throw $e;
        }
    }

    // Методы для планирования питания
    public function generateMealPlan($options = [])
    {
        try {
            $cacheKey = "spoonacular_meal_plan_" . md5(serialize($options));
            
            return Cache::remember($cacheKey, 3600, function () use ($options) {
                $params = array_merge([
                    'apiKey' => $this->apiKey,
                    'timeFrame' => $options['timeFrame'] ?? 'day',
                    'targetCalories' => $options['targetCalories'] ?? 2000,
                    'diet' => $options['diet'] ?? null,
                    'exclude' => $options['exclude'] ?? null,
                ], $options);

                $response = Http::get("{$this->baseUrl}/mealplanner/generate", $params);

                if (!$response->successful()) {
                    Log::error('Spoonacular meal plan error', [
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                    throw new \Exception('Не удалось сгенерировать план питания');
                }

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('Spoonacular meal plan exception', [
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getMealPlanWeek($username, $hash, $options = [])
    {
        try {
            $cacheKey = "spoonacular_meal_plan_week_{$username}";
            
            return Cache::remember($cacheKey, 1800, function () use ($username, $hash, $options) {
                $params = array_merge([
                    'apiKey' => $this->apiKey,
                    'hash' => $hash,
                ], $options);

                $response = Http::get("{$this->baseUrl}/mealplanner/{$username}/week", $params);

                if (!$response->successful()) {
                    Log::error('Spoonacular meal plan week error', [
                        'username' => $username,
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                    throw new \Exception('Не удалось получить план питания на неделю');
                }

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('Spoonacular meal plan week exception', [
                'message' => $e->getMessage(),
                'username' => $username
            ]);
            throw $e;
        }
    }

    // Методы для анализа питания
    public function analyzeRecipe($title, $ingredients, $instructions = null)
    {
        try {
            $cacheKey = "spoonacular_analyze_recipe_" . md5($title . serialize($ingredients));
            
            return Cache::remember($cacheKey, 3600, function () use ($title, $ingredients, $instructions) {
                $data = [
                    'apiKey' => $this->apiKey,
                    'title' => $title,
                    'ingredients' => $ingredients,
                ];

                if ($instructions) {
                    $data['instructions'] = $instructions;
                }

                $response = Http::post("{$this->baseUrl}/recipes/analyze", $data);

                if (!$response->successful()) {
                    Log::error('Spoonacular analyze recipe error', [
                        'title' => $title,
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                    throw new \Exception('Не удалось проанализировать рецепт');
                }

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('Spoonacular analyze recipe exception', [
                'message' => $e->getMessage(),
                'title' => $title
            ]);
            throw $e;
        }
    }

    public function guessNutritionByDishName($title)
    {
        try {
            $cacheKey = "spoonacular_nutrition_guess_" . md5($title);
            
            return Cache::remember($cacheKey, 3600, function () use ($title) {
                $response = Http::get("{$this->baseUrl}/recipes/guessNutrition", [
                    'apiKey' => $this->apiKey,
                    'title' => $title,
                ]);

                if (!$response->successful()) {
                    Log::error('Spoonacular nutrition guess error', [
                        'title' => $title,
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                    throw new \Exception('Не удалось угадать питательную ценность блюда');
                }

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('Spoonacular nutrition guess exception', [
                'message' => $e->getMessage(),
                'title' => $title
            ]);
            throw $e;
        }
    }

    // Методы для классификации кухонь
    public function classifyCuisine($title, $ingredients)
    {
        try {
            $cacheKey = "spoonacular_cuisine_classify_" . md5($title . serialize($ingredients));
            
            return Cache::remember($cacheKey, 3600, function () use ($title, $ingredients) {
                $response = Http::post("{$this->baseUrl}/recipes/cuisine", [
                    'apiKey' => $this->apiKey,
                    'title' => $title,
                    'ingredientList' => $ingredients,
                ]);

                if (!$response->successful()) {
                    Log::error('Spoonacular cuisine classify error', [
                        'title' => $title,
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                    throw new \Exception('Не удалось классифицировать кухню');
                }

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('Spoonacular cuisine classify exception', [
                'message' => $e->getMessage(),
                'title' => $title
            ]);
            throw $e;
        }
    }
} 