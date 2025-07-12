<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpoonacularService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.spoonacular.com/food';

    public function __construct()
    {
        $this->apiKey = config('services.spoonacular.api_key');
    }

    public function searchProducts($query, $page = 1, $perPage = 20)
    {
        try {
            $cacheKey = "spoonacular_search_{$query}_{$page}_{$perPage}";
            
            return Cache::remember($cacheKey, 300, function () use ($query, $page, $perPage) {
                $response = Http::get("{$this->baseUrl}/products/search", [
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
                $response = Http::get("{$this->baseUrl}/products/upc/{$upc}", [
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

        // Пытаемся получить данные из кеша
        // if (Cache::has($cacheKey)) {
        //     return Cache::get($cacheKey);
        // }

        try {
            $response = Http::withHeaders([
                // 'x-api-key' => $this->apiKey, // Если ключ передается в заголовке
            ])->get("{$this->baseUrl}/products/{$productId}", [
                'apiKey' => $this->apiKey, // Или ключ передается как GET-параметр
            ]);

            if ($response->successful()) {
                $productInfo = $response->json();
                // Кешируем результат на некоторое время (например, на 24 часа)
                // Cache::put($cacheKey, $productInfo, now()->addHours(24));
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
} 