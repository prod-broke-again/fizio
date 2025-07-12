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
} 