<?php

namespace App\Http\Controllers;

use App\Services\FatSecretService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FatSecretController extends Controller
{
    protected FatSecretService $fatSecretService;

    public function __construct(FatSecretService $fatSecretService)
    {
        $this->fatSecretService = $fatSecretService;
    }

    public function getToken(): JsonResponse
    {
        try {
            $token = $this->fatSecretService->getAccessToken();
            return response()->json($token);
        } catch (\Exception $e) {
            Log::error('FatSecret token error in controller', [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Ошибка получения токена',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function searchFoods(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2',
                'page' => 'nullable|integer|min:1',
                'max_results' => 'nullable|integer|min:1|max:50'
            ]);

            $result = $this->fatSecretService->searchFoods(
                $request->input('query'),
                $request->input('page', 1),
                $request->input('max_results', 20)
            );

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('FatSecret search error in controller', [
                'message' => $e->getMessage(),
                'query' => $request->input('query')
            ]);
            return response()->json([
                'error' => 'Ошибка поиска продуктов',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getFood($foodId): JsonResponse
    {
        try {
            $result = $this->fatSecretService->getFood($foodId);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('FatSecret food get error in controller', [
                'message' => $e->getMessage(),
                'food_id' => $foodId
            ]);
            return response()->json([
                'error' => 'Ошибка получения информации о продукте',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * FatSecret Autocomplete (автозаполнение)
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);
        $result = $this->fatSecretService->autocomplete($request->input('query'), 'RU', 'ru');
        return response()->json($result);
    }

    /**
     * FatSecret Image Recognition (распознавание по фото)
     */
    public function recognizeByImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:1100', // до 1.09MB
        ]);
        $image = $request->file('image');
        $result = $this->fatSecretService->recognizeByImage($image, 'RU', 'ru');
        return response()->json($result);
    }

    /**
     * Поиск брендов
     */
    public function searchBrands(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);
        $result = $this->fatSecretService->searchBrands($request->input('query'), 'RU', 'ru');
        return response()->json($result);
    }

    /**
     * Получение категорий продуктов
     */
    public function getCategories(Request $request): JsonResponse
    {
        $result = $this->fatSecretService->getCategories('RU', 'ru');
        return response()->json($result);
    }

    /**
     * Поиск рецептов
     */
    public function searchRecipes(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);
        $result = $this->fatSecretService->searchRecipes($request->input('query'), 'RU', 'ru');
        return response()->json($result);
    }
}
