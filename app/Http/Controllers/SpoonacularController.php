<?php

namespace App\Http\Controllers;

use App\Services\SpoonacularService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class SpoonacularController extends Controller
{
    protected $spoonacularService;

    public function __construct(SpoonacularService $spoonacularService)
    {
        $this->spoonacularService = $spoonacularService;
    }

    // Существующие методы для продуктов
    public function searchProducts(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:50'
            ]);

            $result = $this->spoonacularService->searchProducts(
                $request->input('query'),
                $request->input('page', 1),
                $request->input('per_page', 20)
            );

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Spoonacular search error in controller', [
                'message' => $e->getMessage(),
                'query' => $request->input('query')
            ]);
            return response()->json([
                'error' => 'Ошибка поиска продуктов',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getProductByUPC($upc)
    {
        try {
            $result = $this->spoonacularService->getProductByUPC($upc);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Spoonacular UPC error in controller', [
                'message' => $e->getMessage(),
                'upc' => $upc
            ]);
            return response()->json([
                'error' => 'Ошибка получения информации о продукте',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getProductInformation(int $id): JsonResponse
    {
        try {
            $result = $this->spoonacularService->getProductInformation($id);

            if ($result) {
                return response()->json($result);
            }

            return response()->json(['error' => 'Product not found or API error'], 404);
        } catch (\Exception $e) {
            Log::error('Spoonacular Product Info error in controller', [
                'productId' => $id,
                'message' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    // Новые методы для рецептов
    public function searchRecipes(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2',
                'number' => 'nullable|integer|min:1|max:100',
                'addRecipeNutrition' => 'nullable|boolean',
                'instructionsRequired' => 'nullable|boolean',
                'diet' => 'nullable|string',
                'cuisine' => 'nullable|string',
                'intolerances' => 'nullable|string',
                'maxReadyTime' => 'nullable|integer',
                'minProtein' => 'nullable|integer',
                'maxProtein' => 'nullable|integer',
                'minFat' => 'nullable|integer',
                'maxFat' => 'nullable|integer',
                'minCarbs' => 'nullable|integer',
                'maxCarbs' => 'nullable|integer',
            ]);

            $options = $request->only([
                'number', 'addRecipeNutrition', 'instructionsRequired', 'diet', 
                'cuisine', 'intolerances', 'maxReadyTime', 'minProtein', 
                'maxProtein', 'minFat', 'maxFat', 'minCarbs', 'maxCarbs'
            ]);

            $result = $this->spoonacularService->searchRecipes(
                $request->input('query'),
                $options
            );

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Spoonacular recipes search error in controller', [
                'message' => $e->getMessage(),
                'query' => $request->input('query')
            ]);
            return response()->json([
                'error' => 'Ошибка поиска рецептов',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function searchRecipesByIngredients(Request $request)
    {
        try {
            $request->validate([
                'ingredients' => 'required|array|min:1',
                'ingredients.*' => 'string',
                'number' => 'nullable|integer|min:1|max:100',
                'ranking' => 'nullable|integer|in:1,2',
                'ignorePantry' => 'nullable|boolean',
            ]);

            $result = $this->spoonacularService->searchRecipesByIngredients(
                $request->input('ingredients'),
                $request->only(['number', 'ranking', 'ignorePantry'])
            );

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Spoonacular recipes by ingredients error in controller', [
                'message' => $e->getMessage(),
                'ingredients' => $request->input('ingredients')
            ]);
            return response()->json([
                'error' => 'Ошибка поиска рецептов по ингредиентам',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getRecipeInformation(int $recipeId, Request $request)
    {
        try {
            $request->validate([
                'includeNutrition' => 'nullable|boolean',
            ]);

            $result = $this->spoonacularService->getRecipeInformation(
                $recipeId,
                $request->input('includeNutrition', true)
            );

            if ($result) {
                return response()->json($result);
            }

            return response()->json(['error' => 'Recipe not found or API error'], 404);
        } catch (\Exception $e) {
            Log::error('Spoonacular Recipe Info error in controller', [
                'recipeId' => $recipeId,
                'message' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function getRandomRecipes(Request $request)
    {
        try {
            $request->validate([
                'number' => 'nullable|integer|min:1|max:100',
                'addRecipeNutrition' => 'nullable|boolean',
                'tags' => 'nullable|string',
                'diet' => 'nullable|string',
                'cuisine' => 'nullable|string',
                'intolerances' => 'nullable|string',
            ]);

            $result = $this->spoonacularService->getRandomRecipes(
                $request->only(['number', 'addRecipeNutrition', 'tags', 'diet', 'cuisine', 'intolerances'])
            );

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Spoonacular random recipes error in controller', [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Ошибка получения случайных рецептов',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Методы для ингредиентов
    public function searchIngredients(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2',
                'number' => 'nullable|integer|min:1|max:100',
                'addChildren' => 'nullable|boolean',
                'metaInformation' => 'nullable|boolean',
                'sortDirection' => 'nullable|string|in:asc,desc',
            ]);

            $result = $this->spoonacularService->searchIngredients(
                $request->input('query'),
                $request->only(['number', 'addChildren', 'metaInformation', 'sortDirection'])
            );

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Spoonacular ingredients search error in controller', [
                'message' => $e->getMessage(),
                'query' => $request->input('query')
            ]);
            return response()->json([
                'error' => 'Ошибка поиска ингредиентов',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getIngredientInformation(int $ingredientId, Request $request)
    {
        try {
            $request->validate([
                'amount' => 'nullable|numeric|min:0',
                'unit' => 'nullable|string',
            ]);

            $result = $this->spoonacularService->getIngredientInformation(
                $ingredientId,
                $request->only(['amount', 'unit'])
            );

            if ($result) {
                return response()->json($result);
            }

            return response()->json(['error' => 'Ingredient not found or API error'], 404);
        } catch (\Exception $e) {
            Log::error('Spoonacular Ingredient Info error in controller', [
                'ingredientId' => $ingredientId,
                'message' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function autocompleteIngredientSearch(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:1',
                'number' => 'nullable|integer|min:1|max:100',
            ]);

            $result = $this->spoonacularService->autocompleteIngredientSearch(
                $request->input('query'),
                $request->input('number', 10)
            );

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Spoonacular ingredient autocomplete error in controller', [
                'message' => $e->getMessage(),
                'query' => $request->input('query')
            ]);
            return response()->json([
                'error' => 'Ошибка автодополнения ингредиентов',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Методы для планирования питания
    public function generateMealPlan(Request $request)
    {
        try {
            $request->validate([
                'timeFrame' => 'nullable|string|in:day,week',
                'targetCalories' => 'nullable|integer|min:200|max:8000',
                'diet' => 'nullable|string',
                'exclude' => 'nullable|string',
            ]);

            $result = $this->spoonacularService->generateMealPlan(
                $request->only(['timeFrame', 'targetCalories', 'diet', 'exclude'])
            );

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Spoonacular meal plan error in controller', [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Ошибка генерации плана питания',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getMealPlanWeek(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'hash' => 'required|string',
            ]);

            $result = $this->spoonacularService->getMealPlanWeek(
                $request->input('username'),
                $request->input('hash')
            );

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Spoonacular meal plan week error in controller', [
                'message' => $e->getMessage(),
                'username' => $request->input('username')
            ]);
            return response()->json([
                'error' => 'Ошибка получения плана питания на неделю',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Методы для анализа питания
    public function analyzeRecipe(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|min:1',
                'ingredients' => 'required|string',
                'instructions' => 'nullable|string',
            ]);

            $result = $this->spoonacularService->analyzeRecipe(
                $request->input('title'),
                $request->input('ingredients'),
                $request->input('instructions')
            );

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Spoonacular analyze recipe error in controller', [
                'message' => $e->getMessage(),
                'title' => $request->input('title')
            ]);
            return response()->json([
                'error' => 'Ошибка анализа рецепта',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function guessNutritionByDishName(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|min:1',
            ]);

            $result = $this->spoonacularService->guessNutritionByDishName(
                $request->input('title')
            );

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Spoonacular nutrition guess error in controller', [
                'message' => $e->getMessage(),
                'title' => $request->input('title')
            ]);
            return response()->json([
                'error' => 'Ошибка оценки питательной ценности',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function classifyCuisine(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|min:1',
                'ingredients' => 'required|string',
            ]);

            $result = $this->spoonacularService->classifyCuisine(
                $request->input('title'),
                $request->input('ingredients')
            );

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Spoonacular cuisine classify error in controller', [
                'message' => $e->getMessage(),
                'title' => $request->input('title')
            ]);
            return response()->json([
                'error' => 'Ошибка классификации кухни',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 