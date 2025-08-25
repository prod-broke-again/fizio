<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    protected $fillable = [
        'code', 'product_name', 'generic_name', 'brands', 'categories',
        'ingredients_text', 'countries', 'quantity', 'packaging',
        'image_url', 'image_small_url', 'image_ingredients_url', 'image_nutrition_url',
        'nutriscore_grade', 'nova_group', 'energy-kcal_100g', 'proteins_100g',
        'carbohydrates_100g', 'fat_100g', 'fiber_100g', 'salt_100g',
        'sugars_100g', 'saturated-fat_100g', 'allergens', 'traces',
        'additives', 'labels', 'origins', 'manufacturing_places',
        'created_t', 'last_modified_t', 'unique_scans_n', 'completeness'
    ];

    protected $casts = [
        'created_t' => 'datetime',
        'last_modified_t' => 'datetime',
        'energy-kcal_100g' => 'decimal:2',
        'proteins_100g' => 'decimal:2',
        'carbohydrates_100g' => 'decimal:2',
        'fat_100g' => 'decimal:2',
        'fiber_100g' => 'decimal:2',
        'salt_100g' => 'decimal:2',
        'sugars_100g' => 'decimal:2',
        'saturated-fat_100g' => 'decimal:2',
        'completeness' => 'decimal:2',
    ];

    // Поиск по штрихкоду с кешированием
    public static function findByBarcode($barcode)
    {
        return Cache::remember("product_barcode_{$barcode}", 60*24*30, function() use ($barcode) {
            return static::where('code', $barcode)->first();
        });
    }

    // Поиск по названию с кешированием
    public static function searchByName($query, $limit = 20)
    {
        $cacheKey = "product_search_" . md5($query . $limit);
        
        return Cache::remember($cacheKey, 60*24*30, function() use ($query, $limit) {
            return static::where('product_name', 'LIKE', "%{$query}%")
                        ->orWhere('generic_name', 'LIKE', "%{$query}%")
                        ->orWhere('brands', 'LIKE', "%{$query}%")
                        ->orWhere('ingredients_text', 'LIKE', "%{$query}%")
                        ->orderBy('unique_scans_n', 'desc')
                        ->limit($limit)
                        ->get();
        });
    }

    // Поиск по бренду
    public static function findByBrand($brand, $limit = 20)
    {
        $cacheKey = "product_brand_" . md5($brand . $limit);
        
        return Cache::remember($cacheKey, 60*24*30, function() use ($brand, $limit) {
            return static::where('brands', 'LIKE', "%{$brand}%")
                        ->orderBy('unique_scans_n', 'desc')
                        ->limit($limit)
                        ->get();
        });
    }

    // Поиск по стране
    public static function findByCountry($country, $limit = 20)
    {
        $cacheKey = "product_country_" . md5($country . $limit);
        
        return Cache::remember($cacheKey, 60*24*30, function() use ($country, $limit) {
            return static::where('countries', 'LIKE', "%{$country}%")
                        ->orderBy('unique_scans_n', 'desc')
                        ->limit($limit)
                        ->get();
        });
    }

    // Поиск по Nutri-Score
    public static function findByNutriScore($grade, $limit = 20)
    {
        $cacheKey = "product_nutriscore_{$grade}_{$limit}";
        
        return Cache::remember($cacheKey, 60*24*30, function() use ($grade, $limit) {
            return static::where('nutriscore_grade', $grade)
                        ->orderBy('unique_scans_n', 'desc')
                        ->limit($limit)
                        ->get();
        });
    }

    // Получить популярные продукты
    public static function getPopular($limit = 20)
    {
        return Cache::remember("popular_products_{$limit}", 60*24*30, function() use ($limit) {
            return static::where('unique_scans_n', '>', 0)
                        ->orderBy('unique_scans_n', 'desc')
                        ->limit($limit)
                        ->get();
        });
    }

    // Получить товары СНГ (основные страны)
    public static function getCISProducts($limit = 20)
    {
        $cacheKey = "cis_products_{$limit}";
        
        return Cache::remember($cacheKey, 60*24*30, function() use ($limit) {
            return static::where(function($query) {
                $query->where('countries', 'LIKE', '%russia%')
                      ->orWhere('countries', 'LIKE', '%ukraine%')
                      ->orWhere('countries', 'LIKE', '%belarus%')
                      ->orWhere('countries', 'LIKE', '%kazakhstan%')
                      ->orWhere('countries', 'LIKE', '%moldova%');
            })
            ->where(function($query) {
                // Исключаем товары с турецкими, армянскими и другими языками
                $query->whereNull('product_name')
                      ->orWhere('product_name', 'NOT LIKE', '%турецк%')
                      ->orWhere('product_name', 'NOT LIKE', '%армянск%')
                      ->orWhere('product_name', 'NOT LIKE', '%türk%')
                      ->orWhere('product_name', 'NOT LIKE', '%հայկական%');
            })
            ->orderBy('unique_scans_n', 'desc')
            ->limit($limit)
            ->get();
        });
    }

    // Получить только российские товары
    public static function getRussianProducts($limit = 20)
    {
        $cacheKey = "russian_products_{$limit}";
        
        return Cache::remember($cacheKey, 60*24*30, function() use ($limit) {
            return static::where('countries', 'LIKE', '%russia%')
                        ->orderBy('unique_scans_n', 'desc')
                        ->limit($limit)
                        ->get();
        });
    }

    // Получить товары, произведенные в СНГ
    public static function getCISManufactured($limit = 20)
    {
        $cacheKey = "cis_manufactured_{$limit}";
        
        return Cache::remember($cacheKey, 60*24*30, function() use ($limit) {
            return static::where(function($query) {
                $query->where('manufacturing_places', 'LIKE', '%russia%')
                      ->orWhere('manufacturing_places', 'LIKE', '%ukraine%')
                      ->orWhere('manufacturing_places', 'LIKE', '%belarus%')
                      ->orWhere('manufacturing_places', 'LIKE', '%kazakhstan%')
                      ->orWhere('manufacturing_places', 'LIKE', '%moldova%');
            })
            ->orderBy('unique_scans_n', 'desc')
            ->limit($limit)
            ->get();
        });
    }

    // Проверить, содержит ли продукт аллерген
    public function hasAllergen($allergen)
    {
        return $this->allergens && str_contains(strtolower($this->allergens), strtolower($allergen));
    }

    // Получить рейтинг полноты данных
    public function getCompletenessPercentage()
    {
        return $this->completeness ?? 0;
    }
}
