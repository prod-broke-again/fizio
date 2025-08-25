<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\MealItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MealItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Добавить продукт в приём пищи.
     */
    public function store(Request $request, Meal $meal): JsonResponse
    {
        try {
            // Проверяем, что пользователь владеет приёмом пищи
            if ($meal->user_id !== Auth::id()) {
                return response()->json(['message' => 'Доступ запрещён'], 403);
            }

            // Валидация данных
            $validated = $request->validate([
                'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')],
                'free_text' => ['nullable', 'string', 'max:255'],
                'grams' => ['nullable', 'numeric', 'min:0', 'max:10000'],
                'servings' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'calories' => ['required', 'numeric', 'min:0', 'max:10000'],
                'proteins' => ['required', 'numeric', 'min:0', 'max:1000'],
                'fats' => ['required', 'numeric', 'min:0', 'max:1000'],
                'carbs' => ['required', 'numeric', 'min:0', 'max:1000'],
            ]);

            // Проверяем, что указан либо product_id, либо free_text
            if (empty($validated['product_id']) && empty($validated['free_text'])) {
                return response()->json([
                    'message' => 'Необходимо указать либо product_id, либо free_text'
                ], 422);
            }

            // Если указан product_id, получаем информацию о продукте
            if (!empty($validated['product_id'])) {
                $product = Product::find($validated['product_id']);
                if ($product) {
                    // Если указаны граммы, пересчитываем БЖУ/калории
                    if (!empty($validated['grams'])) {
                        $ratio = $validated['grams'] / 100; // 100г = базовая порция
                        $validated['calories'] = $product->calories * $ratio;
                        $validated['proteins'] = $product->proteins * $ratio;
                        $validated['fats'] = $product->fats * $ratio;
                        $validated['carbs'] = $product->carbs * $ratio;
                    }
                    // Если указаны порции, пересчитываем БЖУ/калории
                    elseif (!empty($validated['servings'])) {
                        $validated['calories'] = $product->calories * $validated['servings'];
                        $validated['proteins'] = $product->proteins * $validated['servings'];
                        $validated['fats'] = $product->fats * $validated['servings'];
                        $validated['carbs'] = $product->carbs * $validated['servings'];
                    }
                }
            }

            // Создаём элемент приёма пищи
            $mealItem = $meal->items()->create($validated);

            Log::info('Добавлен продукт в приём пищи', [
                'meal_id' => $meal->id,
                'meal_item_id' => $mealItem->id,
                'product_name' => $mealItem->product_name
            ]);

            return response()->json([
                'message' => 'Продукт успешно добавлен в приём пищи',
                'meal_item' => $mealItem->load('product'),
                'meal_totals' => [
                    'calories' => $meal->fresh()->total_calories,
                    'proteins' => $meal->fresh()->total_proteins,
                    'fats' => $meal->fresh()->total_fats,
                    'carbs' => $meal->fresh()->total_carbs,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Ошибка при добавлении продукта в приём пищи', [
                'meal_id' => $meal->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Ошибка при добавлении продукта в приём пищи'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Обновить элемент приёма пищи.
     */
    public function update(Request $request, Meal $meal, MealItem $mealItem): JsonResponse
    {
        try {
            // Проверяем, что пользователь владеет приёмом пищи
            if ($meal->user_id !== Auth::id()) {
                return response()->json(['message' => 'Доступ запрещён'], 403);
            }

            // Проверяем, что элемент принадлежит указанному приёму пищи
            if ($mealItem->meal_id !== $meal->id) {
                return response()->json(['message' => 'Элемент не найден в указанном приёме пищи'], 404);
            }

            // Валидация данных
            $validated = $request->validate([
                'grams' => ['nullable', 'numeric', 'min:0', 'max:10000'],
                'servings' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'calories' => ['sometimes', 'numeric', 'min:0', 'max:10000'],
                'proteins' => ['sometimes', 'numeric', 'min:0', 'max:1000'],
                'fats' => ['sometimes', 'numeric', 'min:0', 'max:1000'],
                'carbs' => ['sometimes', 'numeric', 'min:0', 'max:1000'],
            ]);

            // Если указаны граммы или порции, пересчитываем БЖУ/калории
            if (!empty($validated['grams']) || !empty($validated['servings'])) {
                if ($mealItem->product_id && $mealItem->product) {
                    $product = $mealItem->product;
                    
                    if (!empty($validated['grams'])) {
                        $ratio = $validated['grams'] / 100;
                        $validated['calories'] = $product->calories * $ratio;
                        $validated['proteins'] = $product->proteins * $ratio;
                        $validated['fats'] = $product->fats * $ratio;
                        $validated['carbs'] = $product->carbs * $ratio;
                    } elseif (!empty($validated['servings'])) {
                        $validated['calories'] = $product->calories * $validated['servings'];
                        $validated['proteins'] = $product->proteins * $validated['servings'];
                        $validated['fats'] = $product->fats * $validated['servings'];
                        $validated['carbs'] = $product->carbs * $validated['servings'];
                    }
                }
            }

            // Обновляем элемент
            $mealItem->update($validated);

            Log::info('Обновлён элемент приёма пищи', [
                'meal_id' => $meal->id,
                'meal_item_id' => $mealItem->id,
                'product_name' => $mealItem->product_name
            ]);

            return response()->json([
                'message' => 'Элемент приёма пищи успешно обновлён',
                'meal_item' => $mealItem->fresh()->load('product'),
                'meal_totals' => [
                    'calories' => $meal->fresh()->total_calories,
                    'proteins' => $meal->fresh()->total_proteins,
                    'fats' => $meal->fresh()->total_fats,
                    'carbs' => $meal->fresh()->total_carbs,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении элемента приёма пищи', [
                'meal_id' => $meal->id,
                'meal_item_id' => $mealItem->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Ошибка при обновлении элемента приёма пищи'
            ], 500);
        }
    }

    /**
     * Удалить элемент приёма пищи.
     */
    public function destroy(Meal $meal, MealItem $mealItem): JsonResponse
    {
        try {
            // Проверяем, что пользователь владеет приёмом пищи
            if ($meal->user_id !== Auth::id()) {
                return response()->json(['message' => 'Доступ запрещён'], 403);
            }

            // Проверяем, что элемент принадлежит указанному приёму пищи
            if ($mealItem->meal_id !== $meal->id) {
                return response()->json(['message' => 'Элемент не найден в указанном приёме пищи'], 404);
            }

            $productName = $mealItem->product_name;
            
            // Удаляем элемент
            $mealItem->delete();

            Log::info('Удалён элемент приёма пищи', [
                'meal_id' => $meal->id,
                'meal_item_id' => $mealItem->id,
                'product_name' => $productName
            ]);

            return response()->json([
                'message' => 'Элемент приёма пищи успешно удалён',
                'meal_totals' => [
                    'calories' => $meal->fresh()->total_calories,
                    'proteins' => $meal->fresh()->total_proteins,
                    'fats' => $meal->fresh()->total_fats,
                    'carbs' => $meal->fresh()->total_carbs,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка при удалении элемента приёма пищи', [
                'meal_id' => $meal->id,
                'meal_item_id' => $mealItem->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Ошибка при удалении элемента приёма пищи'
            ], 500);
        }
    }

    /**
     * Пакетное добавление элементов в существующий приём пищи
     */
    public function bulkStore(Request $request, Meal $meal): JsonResponse
    {
        try {
            // Проверяем, что пользователь владеет приёмом пищи
            if ($meal->user_id !== Auth::id()) {
                return response()->json(['message' => 'Доступ запрещён'], 403);
            }

            // Валидация данных
            $validated = $request->validate([
                'strategy' => 'required|in:append,overwrite',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'nullable|exists:products,code', // Проверяем по code
                'items.*.free_text' => 'nullable|string|max:255',
                'items.*.grams' => 'nullable|numeric|min:0|max:2000',
                'items.*.servings' => 'nullable|numeric|min:0|max:50',
                'items.*.calories' => 'nullable|numeric|min:0|max:10000',
                'items.*.proteins' => 'nullable|numeric|min:0|max:1000',
                'items.*.fats' => 'nullable|numeric|min:0|max:1000',
                'items.*.carbs' => 'nullable|numeric|min:0|max:1000',
            ]);

            // Проверяем, что указан либо product_id, либо free_text
            foreach ($validated['items'] as $item) {
                if (empty($item['product_id']) && empty($item['free_text'])) {
                    return response()->json([
                        'message' => 'Необходимо указать либо product_id, либо free_text для каждого элемента'
                    ], 422);
                }
            }

            // Если стратегия overwrite, удаляем старые элементы
            if ($validated['strategy'] === 'overwrite') {
                $meal->items()->delete();
            }

            $addedItems = [];
            
            // Добавляем новые элементы
            foreach ($validated['items'] as $item) {
                $mealItemData = $this->prepareMealItemData($item);
                
                if ($mealItemData) {
                    $mealItem = $meal->items()->create($mealItemData);
                    $addedItems[] = $mealItem;
                }
            }

            // Пересчитываем общие показатели приёма пищи
            $this->recalculateMealTotals($meal);

            Log::info('Пакетно добавлены элементы в приём пищи', [
                'meal_id' => $meal->id,
                'strategy' => $validated['strategy'],
                'items_count' => count($addedItems)
            ]);

            return response()->json([
                'message' => 'Элементы успешно добавлены в приём пищи',
                'strategy' => $validated['strategy'],
                'added_items' => $addedItems,
                'meal_totals' => [
                    'calories' => $meal->fresh()->calories,
                    'proteins' => $meal->fresh()->proteins,
                    'fats' => $meal->fresh()->fats,
                    'carbs' => $meal->fresh()->carbs,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Ошибка при пакетном добавлении элементов в приём пищи', [
                'meal_id' => $meal->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Ошибка при пакетном добавлении элементов в приём пищи'
            ], 500);
        }
    }

    /**
     * Подготовка данных для элемента приёма пищи
     */
    private function prepareMealItemData(array $item): ?array
    {
        if (isset($item['product_id'])) {
            // Продукт из локальной БД - ищем по code, но используем id
            $product = Product::where('code', $item['product_id'])->first();
            
            if (!$product) {
                return null;
            }

            $grams = $item['grams'] ?? 0;
            $ratio = $grams / 100;

            return [
                'product_id' => $product->id, // Используем id продукта
                'free_text' => null,
                'grams' => $grams,
                'servings' => $item['servings'] ?? null,
                'calories' => round(($product->{'energy-kcal_100g'} ?? 0) * $ratio, 1),
                'proteins' => round(($product->proteins_100g ?? 0) * $ratio, 1),
                'fats' => round(($product->fat_100g ?? 0) * $ratio, 1),
                'carbs' => round(($product->carbohydrates_100g ?? 0) * $ratio, 1)
            ];
        } else {
            // Свободный текст
            return [
                'product_id' => null,
                'free_text' => $item['free_text'],
                'grams' => $item['grams'] ?? null,
                'servings' => $item['servings'] ?? null,
                'calories' => $item['calories'] ?? 0,
                'proteins' => $item['proteins'] ?? 0,
                'fats' => $item['fats'] ?? 0,
                'carbs' => $item['carbs'] ?? 0
            ];
        }
    }

    /**
     * Пересчитывает общие показатели приёма пищи
     */
    private function recalculateMealTotals(Meal $meal): void
    {
        $totals = $meal->items()->selectRaw('
            SUM(calories) as total_calories,
            SUM(proteins) as total_proteins,
            SUM(fats) as total_fats,
            SUM(carbs) as total_carbs
        ')->first();

        $meal->update([
            'calories' => $totals->total_calories ?? 0,
            'proteins' => $totals->total_proteins ?? 0,
            'fats' => $totals->total_fats ?? 0,
            'carbs' => $totals->total_carbs ?? 0
        ]);
    }
}
