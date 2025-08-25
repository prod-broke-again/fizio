<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LocalProductsController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
{
    protected LocalProductsController $localProductsController;

    public function __construct(LocalProductsController $localProductsController)
    {
        $this->localProductsController = $localProductsController;
    }

    /**
     * Поиск продуктов в локальной базе данных
     */
    public function searchLocal(Request $request): JsonResponse
    {
        try {
            // Вызываем существующий метод поиска
            $response = $this->localProductsController->searchProducts($request);
            
            // Если ответ уже в нужном формате, возвращаем как есть
            if ($response->getStatusCode() === 200) {
                $data = $response->getData(true);
                
                // Нормализуем ответ к единому формату
                $normalizedData = $this->normalizeSearchResponse($data);
                
                return response()->json($normalizedData);
            }
            
            return $response;
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка поиска продуктов',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получение продукта по ID
     */
    public function showLocal(string $id): JsonResponse
    {
        try {
            // Вызываем существующий метод получения продукта
            $response = $this->localProductsController->getProductByBarcode($id);
            
            if ($response->getStatusCode() === 200) {
                $data = $response->getData(true);
                
                // Нормализуем ответ к единому формату
                $normalizedData = $this->normalizeProductResponse($data);
                
                return response()->json($normalizedData);
            }
            
            return $response;
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка получения продукта',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Нормализация ответа поиска
     */
    private function normalizeSearchResponse(array $data): array
    {
        $normalized = [
            'items' => [],
            'pagination' => [
                'page' => 1,
                'pages' => 1,
                'total' => 0
            ]
        ];

        // Обрабатываем продукты
        if (isset($data['products'])) {
            foreach ($data['products'] as $product) {
                $normalized['items'][] = [
                    'id' => $product['code'] ?? $product['id'] ?? null,
                    'provider' => 'local',
                    'title' => $product['product_name'] ?? $product['name'] ?? '',
                    'image' => $product['image_url'] ?? $product['image'] ?? null,
                    'per100' => [
                        'calories' => $product['energy-kcal_100g'] ?? $product['calories'] ?? 0,
                        'proteins' => $product['proteins_100g'] ?? $product['proteins'] ?? 0,
                        'fats' => $product['fat_100g'] ?? $product['fats'] ?? 0,
                        'carbs' => $product['carbohydrates_100g'] ?? $product['carbs'] ?? 0
                    ],
                    'perServing' => null
                ];
            }
        }

        // Обрабатываем пагинацию
        if (isset($data['pagination'])) {
            $normalized['pagination'] = $data['pagination'];
        } elseif (isset($data['total'])) {
            $normalized['pagination']['total'] = $data['total'];
        }

        return $normalized;
    }

    /**
     * Нормализация ответа продукта
     */
    private function normalizeProductResponse(array $data): array
    {
        if (isset($data['product'])) {
            $product = $data['product'];
        } else {
            $product = $data;
        }

        return [
            'id' => $product['code'] ?? $product['id'] ?? null,
            'provider' => 'local',
            'title' => $product['product_name'] ?? $product['name'] ?? '',
            'image' => $product['image_url'] ?? $product['image'] ?? null,
            'per100' => [
                'calories' => $product['energy-kcal_100g'] ?? $product['calories'] ?? 0,
                'proteins' => $product['proteins_100g'] ?? $product['proteins'] ?? 0,
                'fats' => $product['fat_100g'] ?? $product['fats'] ?? 0,
                'carbs' => $product['carbohydrates_100g'] ?? $product['carbs'] ?? 0
            ],
            'perServing' => null,
            'description' => $product['ingredients_text'] ?? $product['description'] ?? null,
            'brands' => $product['brands'] ?? null,
            'categories' => $product['categories'] ?? null
        ];
    }
}
