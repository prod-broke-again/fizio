<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\MealItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MealsApplyController extends Controller
{
    /**
     * Превью корзины (без записи)
     */
    public function preview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,code', // Проверяем по code
            'items.*.free_text' => 'nullable|string|max:255',
            'items.*.grams' => 'nullable|numeric|min:0|max:2000',
            'items.*.servings' => 'nullable|numeric|min:0|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            $items = [];
            $totals = [
                'calories' => 0,
                'proteins' => 0,
                'fats' => 0,
                'carbs' => 0
            ];

            foreach ($request->items as $item) {
                $calculatedItem = $this->calculateItemNutrition($item);
                
                if ($calculatedItem) {
                    $items[] = $calculatedItem;
                    
                    // Суммируем в общие итоги
                    $totals['calories'] += $calculatedItem['calories'];
                    $totals['proteins'] += $calculatedItem['proteins'];
                    $totals['fats'] += $calculatedItem['fats'];
                    $totals['carbs'] += $calculatedItem['carbs'];
                }
            }

            return response()->json([
                'items' => $items,
                'totals' => $totals
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка расчета питания',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Применить корзину в ежедневный план
     */
    public function apply(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'apply_to' => 'required|array',
            'apply_to.dates' => 'nullable|array',
            'apply_to.dates.*' => 'date_format:Y-m-d',
            'apply_to.date_range' => 'nullable|array',
            'apply_to.date_range.from' => 'nullable|date_format:Y-m-d',
            'apply_to.date_range.to' => 'nullable|date_format:Y-m-d',
            'apply_to.weekdays' => 'nullable|array',
            'apply_to.weekdays.*' => 'integer|min:1|max:7',
            'timezone' => 'nullable|string',
            'type' => 'required|in:breakfast,lunch,dinner',
            'strategy' => 'required|in:append,overwrite,skip',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,code', // Проверяем по code
            'items.*.free_text' => 'nullable|string|max:255',
            'items.*.grams' => 'nullable|numeric|min:0|max:2000',
            'items.*.servings' => 'nullable|numeric|min:0|max:50',
            'note' => 'nullable|string|max:500',
            'idempotency_key' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            // Разворачиваем apply_to в список дат
            $dates = $this->expandApplyToDates($request->apply_to, $request->timezone ?? 'UTC');
            
            if (empty($dates)) {
                return response()->json([
                    'error' => 'Неверные параметры дат',
                    'message' => 'Не удалось определить даты для применения'
                ], 422);
            }

            $results = [];
            $totalsAggregated = [
                'calories' => 0,
                'proteins' => 0,
                'fats' => 0,
                'carbs' => 0,
                'days_affected' => 0
            ];

            // Обрабатываем каждую дату
            foreach ($dates as $date) {
                $result = $this->applyToDate($date, $request->type, $request->strategy, $request->items, $request->note);
                
                if ($result) {
                    $results[] = $result;
                    $totalsAggregated['calories'] += $result['totals']['calories'];
                    $totalsAggregated['proteins'] += $result['totals']['proteins'];
                    $totalsAggregated['fats'] += $result['totals']['fats'];
                    $totalsAggregated['carbs'] += $result['totals']['carbs'];
                    $totalsAggregated['days_affected']++;
                }
            }

            return response()->json([
                'results' => $results,
                'totals_aggregated' => $totalsAggregated
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка применения питания',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Расчет питания для одного элемента
     */
    private function calculateItemNutrition(array $item): ?array
    {
        // Проверяем, что указан либо product_id, либо free_text
        if (!isset($item['product_id']) && !isset($item['free_text'])) {
            return null;
        }

        // Проверяем, что указан либо grams, либо servings
        if (!isset($item['grams']) && !isset($item['servings'])) {
            return null;
        }

        if (isset($item['product_id'])) {
            // Продукт из локальной БД - ищем по code, но используем id
            $product = Product::where('code', $item['product_id'])->first();
            
            if (!$product) {
                return null;
            }

            $grams = $item['grams'] ?? 0;
            $ratio = $grams / 100;

            return [
                'ref' => ['product_id' => $product->id], // Используем id продукта
                'calories' => round(($product->{'energy-kcal_100g'} ?? 0) * $ratio, 1),
                'proteins' => round(($product->proteins_100g ?? 0) * $ratio, 1),
                'fats' => round(($product->fat_100g ?? 0) * $ratio, 1),
                'carbs' => round(($product->carbohydrates_100g ?? 0) * $ratio, 1)
            ];
        } else {
            // Свободный текст
            return [
                'ref' => ['free_text' => $item['free_text']],
                'calories' => $item['calories'] ?? 0,
                'proteins' => $item['proteins'] ?? 0,
                'fats' => $item['fats'] ?? 0,
                'carbs' => $item['carbs'] ?? 0
            ];
        }
    }

    /**
     * Разворачивает apply_to в список дат
     */
    private function expandApplyToDates(array $applyTo, string $timezone): array
    {
        $dates = [];

        if (isset($applyTo['dates'])) {
            $dates = $applyTo['dates'];
        } elseif (isset($applyTo['date_range'])) {
            $from = Carbon::parse($applyTo['date_range']['from'], $timezone);
            $to = Carbon::parse($applyTo['date_range']['to'], $timezone);
            
            $current = $from->copy();
            while ($current->lte($to)) {
                $dates[] = $current->format('Y-m-d');
                $current->addDay();
            }
        }

        // Фильтруем по дням недели, если указаны
        if (isset($applyTo['weekdays']) && !empty($applyTo['weekdays'])) {
            $dates = array_filter($dates, function($date) use ($applyTo) {
                $weekday = Carbon::parse($date)->dayOfWeek;
                return in_array($weekday, $applyTo['weekdays']);
            });
        }

        return array_values($dates);
    }

    /**
     * Применяет питание к конкретной дате
     */
    private function applyToDate(string $date, string $type, string $strategy, array $items, ?string $note): ?array
    {
        return DB::transaction(function () use ($date, $type, $strategy, $items, $note) {
            $user = auth()->user();
            
            // Находим или создаем прием пищи
            $meal = Meal::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $date,
                    'type' => $type
                ],
                [
                    'name' => $this->getMealTypeName($type),
                    'calories' => 0,
                    'proteins' => 0,
                    'fats' => 0,
                    'carbs' => 0,
                    'completed' => false
                ]
            );

            // Проверяем стратегию
            if ($strategy === 'skip' && $meal->items()->count() > 0) {
                return null; // Пропускаем дату
            }

            if ($strategy === 'overwrite') {
                // Удаляем старые элементы
                $meal->items()->delete();
            }

            // Добавляем новые элементы
            $mealItems = [];
            foreach ($items as $item) {
                $calculatedItem = $this->calculateItemNutrition($item);
                
                if ($calculatedItem) {
                    $mealItem = new MealItem([
                        'meal_id' => $meal->id,
                        'product_id' => $calculatedItem['ref']['product_id'] ?? null, // Используем id из рассчитанного элемента
                        'free_text' => $calculatedItem['ref']['free_text'] ?? null,
                        'grams' => $item['grams'] ?? null,
                        'servings' => $item['servings'] ?? null,
                        'calories' => $calculatedItem['calories'],
                        'proteins' => $calculatedItem['proteins'],
                        'fats' => $calculatedItem['fats'],
                        'carbs' => $calculatedItem['carbs']
                    ]);
                    
                    $mealItem->save();
                    $mealItems[] = $mealItem;
                }
            }

            // Пересчитываем агрегаты приема пищи
            $this->recalculateMealTotals($meal);

            return [
                'date' => $date,
                'meal_id' => $meal->id,
                'status' => $strategy === 'overwrite' ? 'updated' : 'created',
                'totals' => [
                    'calories' => $meal->calories,
                    'proteins' => $meal->proteins,
                    'fats' => $meal->fats,
                    'carbs' => $meal->carbs
                ]
            ];
        });
    }

    /**
     * Пересчитывает общие показатели приема пищи
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

    /**
     * Получает название типа приема пищи
     */
    private function getMealTypeName(string $type): string
    {
        return match($type) {
            'breakfast' => 'Завтрак',
            'lunch' => 'Обед',
            'dinner' => 'Ужин',
            default => 'Прием пищи'
        };
    }
}
