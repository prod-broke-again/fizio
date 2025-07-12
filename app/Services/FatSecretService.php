<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FatSecretService
{
    protected $clientId;
    protected $clientSecret;
    protected $apiUrl;
    protected $oauthUrl;

    public function __construct()
    {
        $this->clientId = config('services.fatsecret.client_id');
        $this->clientSecret = config('services.fatsecret.client_secret');
        $this->apiUrl = config('services.fatsecret.api_url');
        $this->oauthUrl = config('services.fatsecret.oauth_url');
    }

    /**
     * Получение токена доступа с указанным scope
     * 
     * @param string $scope Scope для токена (basic, image-recognition, premier)
     * @return array
     */
    public function getAccessToken(string $scope = 'basic')
    {
        $cacheKey = "fatsecret_token_{$scope}";
        return Cache::remember($cacheKey, 86300, function () use ($scope) {
            try {
                $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                    ->asForm()
                    ->post($this->oauthUrl, [
                        'grant_type' => 'client_credentials',
                        'scope' => $scope
                    ]);

                if (!$response->successful()) {
                    Log::error('FatSecret token error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'client_id' => substr($this->clientId, 0, 5) . '...',
                        'scope' => $scope
                    ]);
                    throw new \Exception('Failed to get FatSecret access token: ' . $response->body());
                }

                $data = $response->json();
                if (!isset($data['access_token']) || !isset($data['token_type']) || !isset($data['expires_in'])) {
                    Log::error('FatSecret invalid token response', [
                        'response' => $data,
                        'scope' => $scope
                    ]);
                    throw new \Exception('Invalid token response from FatSecret');
                }

                return $data;
            } catch (\Exception $e) {
                Log::error('FatSecret token exception', [
                    'message' => $e->getMessage(),
                    'client_id' => substr($this->clientId, 0, 5) . '...',
                    'scope' => $scope
                ]);
                throw $e;
            }
        });
    }

    public function searchFoods($query, $page = 1, $maxResults = 20)
    {
        try {
            $token = $this->getAccessToken();
            
            $cacheKey = "fatsecret_search_{$query}_{$page}_{$maxResults}";
            
            return Cache::remember($cacheKey, 300, function () use ($token, $query, $page, $maxResults) {
                $response = Http::withToken($token['access_token'])
                    ->get($this->apiUrl, [
                        'method' => 'foods.search',
                        'search_expression' => $query,
                        'page_number' => $page,
                        'max_results' => $maxResults,
                        'format' => 'json'
                    ]);

                if (!$response->successful()) {
                    Log::error('FatSecret search error', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    throw new \Exception('Failed to search foods');
                }

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('FatSecret search exception', [
                'message' => $e->getMessage(),
                'query' => $query
            ]);
            throw $e;
        }
    }

    public function getFood($foodId)
    {
        try {
            $token = $this->getAccessToken();
            
            $cacheKey = "fatsecret_food_{$foodId}";
            
            return Cache::remember($cacheKey, 86400, function () use ($token, $foodId) {
                $response = Http::withToken($token['access_token'])
                    ->get($this->apiUrl, [
                        'method' => 'food.get',
                        'food_id' => $foodId,
                        'format' => 'json'
                    ]);

                if (!$response->successful()) {
                    Log::error('FatSecret food get error', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    throw new \Exception('Failed to get food details');
                }

                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('FatSecret food get exception', [
                'message' => $e->getMessage(),
                'food_id' => $foodId
            ]);
            throw $e;
        }
    }

    /**
     * FatSecret Autocomplete (автозаполнение)
     */
    public function autocomplete($query, $region = 'RU', $language = 'ru')
    {
        try {
            $token = $this->getAccessToken();
            $cacheKey = "fatsecret_autocomplete_{$query}_{$region}_{$language}";
            return Cache::remember($cacheKey, 300, function () use ($token, $query, $region, $language) {
                $response = Http::withToken($token['access_token'])
                    ->get($this->apiUrl, [
                        'method' => 'foods.autocomplete',
                        'search_expression' => $query,
                        'region' => $region,
                        'language' => $language,
                        'format' => 'json'
                    ]);
                if (!$response->successful()) {
                    Log::error('FatSecret autocomplete error', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    throw new \Exception('Failed to autocomplete foods');
                }
                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('FatSecret autocomplete exception', [
                'message' => $e->getMessage(),
                'query' => $query
            ]);
            throw $e;
        }
    }

    /**
     * FatSecret Image Recognition (распознавание по фото)
     */
    public function recognizeByImage($image, $region = 'RU', $language = 'ru')
    {
        try {
            // Получаем токен с scope image-recognition
            $token = $this->getAccessToken('image-recognition');
            
            $cacheKey = 'fatsecret_image_recognition_' . md5($image->getRealPath()) . "_{$region}_{$language}";
            return Cache::remember($cacheKey, 300, function () use ($token, $image, $region, $language) {
                $imageData = base64_encode(file_get_contents($image->getRealPath()));
                $response = Http::withToken($token['access_token'])
                    ->post('https://platform.fatsecret.com/rest/image-recognition/v2', [
                        'image_b64' => $imageData,
                        'region' => $region,
                        'language' => $language,
                        'include_food_data' => true
                    ]);
                if (!$response->successful()) {
                    Log::error('FatSecret image recognition error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'scope' => 'image-recognition'
                    ]);
                    throw new \Exception('Failed to recognize food by image: ' . $response->body());
                }
                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('FatSecret image recognition exception', [
                'message' => $e->getMessage(),
                'scope' => 'image-recognition'
            ]);
            throw $e;
        }
    }

    /**
     * Поиск брендов
     */
    public function searchBrands($query, $region = 'RU', $language = 'ru')
    {
        try {
            $token = $this->getAccessToken();
            $cacheKey = "fatsecret_brands_{$query}_{$region}_{$language}";
            return Cache::remember($cacheKey, 300, function () use ($token, $query, $region, $language) {
                $response = Http::withToken($token['access_token'])
                    ->get($this->apiUrl, [
                        'method' => 'brands.search',
                        'search_expression' => $query,
                        'region' => $region,
                        'language' => $language,
                        'format' => 'json'
                    ]);
                if (!$response->successful()) {
                    Log::error('FatSecret brands search error', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    throw new \Exception('Failed to search brands');
                }
                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('FatSecret brands search exception', [
                'message' => $e->getMessage(),
                'query' => $query
            ]);
            throw $e;
        }
    }

    /**
     * Получение категорий продуктов
     */
    public function getCategories($region = 'RU', $language = 'ru')
    {
        try {
            $token = $this->getAccessToken();
            $cacheKey = "fatsecret_categories_{$region}_{$language}";
            return Cache::remember($cacheKey, 86400, function () use ($token, $region, $language) {
                $response = Http::withToken($token['access_token'])
                    ->get($this->apiUrl, [
                        'method' => 'food_categories.get',
                        'region' => $region,
                        'language' => $language,
                        'format' => 'json'
                    ]);
                if (!$response->successful()) {
                    Log::error('FatSecret categories error', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    throw new \Exception('Failed to get food categories');
                }
                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('FatSecret categories exception', [
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Поиск рецептов
     */
    public function searchRecipes($query, $region = 'RU', $language = 'ru')
    {
        try {
            $token = $this->getAccessToken();
            $cacheKey = "fatsecret_recipes_{$query}_{$region}_{$language}";
            return Cache::remember($cacheKey, 300, function () use ($token, $query, $region, $language) {
                $response = Http::withToken($token['access_token'])
                    ->get($this->apiUrl, [
                        'method' => 'recipes.search',
                        'search_expression' => $query,
                        'region' => $region,
                        'language' => $language,
                        'format' => 'json'
                    ]);
                if (!$response->successful()) {
                    Log::error('FatSecret recipes search error', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    throw new \Exception('Failed to search recipes');
                }
                return $response->json();
            });
        } catch (\Exception $e) {
            Log::error('FatSecret recipes search exception', [
                'message' => $e->getMessage(),
                'query' => $query
            ]);
            throw $e;
        }
    }
} 