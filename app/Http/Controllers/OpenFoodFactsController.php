<?php

namespace App\Http\Controllers;

use App\Services\OpenFoodFactsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OpenFoodFactsController extends Controller
{
    protected $openFoodFactsService;

    public function __construct(OpenFoodFactsService $openFoodFactsService)
    {
        $this->openFoodFactsService = $openFoodFactsService;
    }

    public function searchProducts(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:50'
            ]);

            $result = $this->openFoodFactsService->searchProducts(
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
            Log::error('OpenFoodFacts search error in controller', [
                'message' => $e->getMessage(),
                'query' => $request->input('query')
            ]);
            return response()->json([
                'error' => 'Ошибка поиска продуктов',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getProductByBarcode($barcode)
    {
        try {
            $result = $this->openFoodFactsService->getProductByBarcode($barcode);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('OpenFoodFacts barcode error in controller', [
                'message' => $e->getMessage(),
                'barcode' => $barcode
            ]);
            return response()->json([
                'error' => 'Ошибка получения информации о продукте',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 