<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OpenFoodFacts\Api;

class OpenFoodFactsService
{
    protected $api;
    protected $cacheTime = [
        'search' => 3600, // 1 час для поиска
        'product' => 86400 // 24 часа для продукта
    ];

    public function __construct()
    {
        $this->api = new Api('food', 'world');
    }

    public function searchProducts($query, $page = 1, $perPage = 20)
    {
        try {
            $cacheKey = "openfoodfacts_search_{$query}_{$page}_{$perPage}";
            
            return Cache::remember($cacheKey, $this->cacheTime['search'], function () use ($query) {
                $results = $this->api->search($query);
                
                if (!$results) {
                    Log::error('OpenFoodFacts search error', [
                        'query' => $query
                    ]);
                    throw new \Exception('Не удалось найти продукты');
                }

                // Преобразуем коллекцию в массив
                $products = [];
                foreach ($results as $product) {
                    $products[] = $product->getData();
                }

                return [
                    'products' => $products,
                    'total' => count($products)
                ];
            });
        } catch (\Exception $e) {
            Log::error('OpenFoodFacts search exception', [
                'message' => $e->getMessage(),
                'query' => $query
            ]);
            throw $e;
        }
    }

    public function getProductByBarcode($barcode)
    {
        try {
            $cacheKey = "openfoodfacts_barcode_{$barcode}";
            
            return Cache::remember($cacheKey, $this->cacheTime['product'], function () use ($barcode) {
                $product = $this->api->getProduct($barcode);
                
                if (!$product) {
                    Log::error('OpenFoodFacts barcode error', [
                        'barcode' => $barcode
                    ]);
                    throw new \Exception('Продукт не найден');
                }

                return $product->getData();
            });
        } catch (\Exception $e) {
            Log::error('OpenFoodFacts barcode exception', [
                'message' => $e->getMessage(),
                'barcode' => $barcode
            ]);
            throw $e;
        }
    }

    /**
     * Очистка кэша для конкретного продукта
     */
    public function clearProductCache($barcode)
    {
        Cache::forget("openfoodfacts_barcode_{$barcode}");
    }

    /**
     * Очистка кэша поиска
     */
    public function clearSearchCache($query, $page = 1, $perPage = 20)
    {
        Cache::forget("openfoodfacts_search_{$query}_{$page}_{$perPage}");
    }
} 