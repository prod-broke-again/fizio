<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LocalProductsService
{
    protected $cacheTime = [
        'search' => 3600, // 1 час для поиска
        'product' => 86400 // 24 часа для продукта
    ];

    public function searchProducts($query, $page = 1, $perPage = 20)
    {
        try {
            $cacheKey = "local_products_search_{$query}_{$page}_{$perPage}";
            
            return Cache::remember($cacheKey, $this->cacheTime['search'], function () use ($query, $page, $perPage) {
                $products = Product::where('product_name', 'like', "%{$query}%")
                    ->orWhere('brands', 'like', "%{$query}%")
                    ->orWhere('categories', 'like', "%{$query}%")
                    ->orWhere('ingredients_text', 'like', "%{$query}%")
                    ->orderBy('unique_scans_n', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);

                return [
                    'products' => $products->items(),
                    'total' => $products->total(),
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'last_page' => $products->lastPage()
                ];
            });
        } catch (\Exception $e) {
            Log::error('Local products search exception', [
                'message' => $e->getMessage(),
                'query' => $query
            ]);
            throw $e;
        }
    }

    public function getProductByBarcode($barcode)
    {
        try {
            $cacheKey = "local_products_barcode_{$barcode}";
            
            return Cache::remember($cacheKey, $this->cacheTime['product'], function () use ($barcode) {
                $product = Product::where('code', $barcode)->first();
                
                if (!$product) {
                    throw new \Exception('Продукт не найден');
                }

                return $product->toArray();
            });
        } catch (\Exception $e) {
            Log::error('Local products barcode exception', [
                'message' => $e->getMessage(),
                'barcode' => $barcode
            ]);
            throw $e;
        }
    }

    public function getCISProducts($page = 1, $perPage = 20)
    {
        try {
            $cacheKey = "local_products_cis_{$page}_{$perPage}";
            
            return Cache::remember($cacheKey, $this->cacheTime['search'], function () use ($page, $perPage) {
                $products = Product::where(function($query) {
                    $query->where('countries', 'LIKE', '%russia%')
                          ->orWhere('countries', 'LIKE', '%ukraine%')
                          ->orWhere('countries', 'LIKE', '%belarus%')
                          ->orWhere('countries', 'LIKE', '%kazakhstan%')
                          ->orWhere('countries', 'LIKE', '%moldova%');
                })
                ->where(function($query) {
                    $query->whereNull('product_name')
                          ->orWhere('product_name', 'NOT LIKE', '%турецк%')
                          ->orWhere('product_name', 'NOT LIKE', '%армянск%')
                          ->orWhere('product_name', 'NOT LIKE', '%türk%')
                          ->orWhere('product_name', 'NOT LIKE', '%հայկական%');
                })
                ->orderBy('unique_scans_n', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

                return [
                    'products' => $products->items(),
                    'total' => $products->total(),
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'last_page' => $products->lastPage()
                ];
            });
        } catch (\Exception $e) {
            Log::error('Local products CIS exception', [
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getRussianProducts($page = 1, $perPage = 20)
    {
        try {
            $cacheKey = "local_products_russian_{$page}_{$perPage}";
            
            return Cache::remember($cacheKey, $this->cacheTime['search'], function () use ($page, $perPage) {
                $products = Product::where('countries', 'LIKE', '%russia%')
                    ->orderBy('unique_scans_n', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);

                return [
                    'products' => $products->items(),
                    'total' => $products->total(),
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'last_page' => $products->lastPage()
                ];
            });
        } catch (\Exception $e) {
            Log::error('Local products Russian exception', [
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Очистка кэша для конкретного продукта
     */
    public function clearProductCache($barcode)
    {
        Cache::forget("local_products_barcode_{$barcode}");
    }

    /**
     * Очистка кэша поиска
     */
    public function clearSearchCache($query, $page = 1, $perPage = 20)
    {
        Cache::forget("local_products_search_{$query}_{$page}_{$perPage}");
    }

    /**
     * Очистка всего кэша продуктов
     */
    public function clearAllCache()
    {
        Cache::flush();
    }
} 