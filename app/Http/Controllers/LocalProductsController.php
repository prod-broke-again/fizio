<?php

namespace App\Http\Controllers;

use App\Services\LocalProductsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocalProductsController extends Controller
{
    protected $localProductsService;

    public function __construct(LocalProductsService $localProductsService)
    {
        $this->localProductsService = $localProductsService;
    }

    public function searchProducts(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:50'
            ]);

            $result = $this->localProductsService->searchProducts(
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
            Log::error('Local products search error in controller', [
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
            $result = $this->localProductsService->getProductByBarcode($barcode);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Local products barcode error in controller', [
                'message' => $e->getMessage(),
                'barcode' => $barcode
            ]);
            return response()->json([
                'error' => 'Ошибка получения информации о продукте',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCISProducts(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:50'
            ]);

            $result = $this->localProductsService->getCISProducts(
                $request->input('page', 1),
                $request->input('per_page', 20)
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Local products CIS error in controller', [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Ошибка получения продуктов СНГ',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getRussianProducts(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:50'
            ]);

            $result = $this->localProductsService->getRussianProducts(
                $request->input('page', 1),
                $request->input('per_page', 20)
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Local products Russian error in controller', [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Ошибка получения российских продуктов',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getStats()
    {
        try {
            $stats = [
                'total_products' => \App\Models\Product::count(),
                'cis_products' => \App\Models\Product::where(function($query) {
                    $query->where('countries', 'LIKE', '%russia%')
                          ->orWhere('countries', 'LIKE', '%ukraine%')
                          ->orWhere('countries', 'LIKE', '%belarus%')
                          ->orWhere('countries', 'LIKE', '%kazakhstan%')
                          ->orWhere('countries', 'LIKE', '%moldova%');
                })->count(),
                'russian_products' => \App\Models\Product::where('countries', 'LIKE', '%russia%')->count(),
                'last_import' => \App\Models\Product::latest()->first()?->created_at
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Local products stats error in controller', [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Ошибка получения статистики',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 