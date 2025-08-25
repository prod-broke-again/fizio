<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\MealItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MealController extends Controller
{
    /**
     * Получить приём пищи с элементами и агрегатами.
     */
    public function show(Meal $meal): JsonResponse
    {
        try {
            // Проверяем, что пользователь владеет приёмом пищи
            if ($meal->user_id !== Auth::id()) {
                return response()->json(['message' => 'Доступ запрещён'], 403);
            }

            // Загружаем элементы приёма пищи с продуктами
            $meal->load(['items.product']);

            // Получаем агрегаты
            $totals = [
                'calories' => $meal->total_calories,
                'proteins' => $meal->total_proteins,
                'fats' => $meal->total_fats,
                'carbs' => $meal->total_carbs,
                'items_count' => $meal->items_count,
            ];

            return response()->json([
                'meal' => $meal,
                'totals' => $totals,
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка при получении приёма пищи', [
                'meal_id' => $meal->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Ошибка при получении приёма пищи'
            ], 500);
        }
    }

    /**
     * Создать новый приём пищи.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Валидация данных
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', 'string', 'max:50'],
                'date' => ['required', 'date'],
                'time' => ['nullable', 'string', 'max:10'],
                'completed' => ['boolean'],
            ]);

            // Создаём приём пищи
            $meal = Auth::user()->meals()->create($validated);

            Log::info('Создан новый приём пищи', [
                'meal_id' => $meal->id,
                'name' => $meal->name,
                'type' => $meal->type,
                'date' => $meal->date
            ]);

            return response()->json([
                'message' => 'Приём пищи успешно создан',
                'meal' => $meal,
                'totals' => [
                    'calories' => 0,
                    'proteins' => 0,
                    'fats' => 0,
                    'carbs' => 0,
                    'items_count' => 0,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Ошибка при создании приёма пищи', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Ошибка при создании приёма пищи'
            ], 500);
        }
    }

    /**
     * Обновить приём пищи.
     */
    public function update(Request $request, Meal $meal): JsonResponse
    {
        try {
            // Проверяем, что пользователь владеет приёмом пищи
            if ($meal->user_id !== Auth::id()) {
                return response()->json(['message' => 'Доступ запрещён'], 403);
            }

            // Валидация данных
            $validated = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'type' => ['sometimes', 'string', 'max:50'],
                'date' => ['sometimes', 'date'],
                'time' => ['nullable', 'string', 'max:10'],
                'completed' => ['sometimes', 'boolean'],
            ]);

            // Обновляем приём пищи
            $meal->update($validated);

            Log::info('Обновлён приём пищи', [
                'meal_id' => $meal->id,
                'name' => $meal->name
            ]);

            return response()->json([
                'message' => 'Приём пищи успешно обновлён',
                'meal' => $meal->fresh(),
                'totals' => [
                    'calories' => $meal->fresh()->total_calories,
                    'proteins' => $meal->fresh()->total_proteins,
                    'fats' => $meal->fresh()->total_fats,
                    'carbs' => $meal->fresh()->total_carbs,
                    'items_count' => $meal->fresh()->items_count,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении приёма пищи', [
                'meal_id' => $meal->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Ошибка при обновлении приёма пищи'
            ], 500);
        }
    }

    /**
     * Удалить приём пищи.
     */
    public function destroy(Meal $meal): JsonResponse
    {
        try {
            // Проверяем, что пользователь владеет приёмом пищи
            if ($meal->user_id !== Auth::id()) {
                return response()->json(['message' => 'Доступ запрещён'], 403);
            }

            $mealName = $meal->name;
            
            // Удаляем приём пищи (items удалятся автоматически благодаря cascade)
            $meal->delete();

            Log::info('Удалён приём пищи', [
                'meal_id' => $meal->id,
                'name' => $mealName
            ]);

            return response()->json([
                'message' => 'Приём пищи успешно удалён'
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка при удалении приёма пищи', [
                'meal_id' => $meal->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Ошибка при удалении приёма пищи'
            ], 500);
        }
    }
}
