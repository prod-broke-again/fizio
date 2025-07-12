<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\Food;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NutritionController extends Controller
{
    /**
     * Получение текущего плана питания пользователя.
     * На данный момент возвращает приемы пищи за текущую неделю.
     * TODO: Реализовать более сложную логику получения "плана", если требуется (например, из отдельной таблицы планов).
     */
    public function getNutritionPlan(Request $request): JsonResponse
    {
        $user = $request->user();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();

        $meals = Meal::where('user_id', $user->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->orderBy('date')
            ->orderBy('type')
            ->get();

        return response()->json([
            'data' => $meals->map(function ($meal) {
                return $this->formatMealResponse($meal);
            }),
            'message' => 'План питания успешно получен'
        ]);
    }

    /**
     * Добавление нового приема пищи.
     */
    public function addMeal(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Завтрак,Обед,Ужин,Перекус',
            'calories' => 'required|numeric|min:0',
            'proteins' => 'required|numeric|min:0',
            'fats' => 'required|numeric|min:0',
            'carbs' => 'required|numeric|min:0',
            'food_id' => 'nullable|string|max:255',
            'date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();
        $user = $request->user();

        $meal = Meal::create([
            'id' => Str::uuid()->toString(),
            'user_id' => $user->id,
            'name' => trim($validatedData['name']),
            'type' => $validatedData['type'],
            'calories' => $validatedData['calories'],
            'proteins' => $validatedData['proteins'],
            'fats' => $validatedData['fats'],
            'carbs' => $validatedData['carbs'],
            'food_id' => $validatedData['food_id'] ?? null,
            'date' => $validatedData['date'],
            'completed' => false,
        ]);

        return response()->json([
            'data' => $this->formatMealResponse($meal),
            'message' => 'Прием пищи успешно добавлен'
        ], 201);
    }

    /**
     * Обновление существующего приема пищи.
     */
    public function updateMeal(Request $request, $mealId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|in:Завтрак,Обед,Ужин,Перекус',
            'calories' => 'sometimes|required|numeric|min:0',
            'proteins' => 'sometimes|required|numeric|min:0',
            'fats' => 'sometimes|required|numeric|min:0',
            'carbs' => 'sometimes|required|numeric|min:0',
            'food_id' => 'nullable|string|max:255',
            'date' => 'sometimes|required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $validator->errors()
            ], 422);
        }

        $meal = Meal::where('id', $mealId)->where('user_id', $request->user()->id)->first();

        if (!$meal) {
            return response()->json([
                'error' => 'Ресурс не найден',
                'message' => 'Прием пищи с указанным ID не найден'
            ], 404);
        }

        $validatedData = $validator->validated();
        if (isset($validatedData['name'])) {
            $validatedData['name'] = trim($validatedData['name']);
        }

        $meal->update($validatedData);

        return response()->json([
            'data' => $this->formatMealResponse($meal->fresh()),
            'message' => 'Прием пищи успешно обновлен'
        ]);
    }

    /**
     * Удаление приема пищи.
     */
    public function deleteMeal(Request $request, $mealId): JsonResponse
    {
        $meal = Meal::where('id', $mealId)->where('user_id', $request->user()->id)->first();

        if (!$meal) {
            return response()->json([
                'error' => 'Ресурс не найден',
                'message' => 'Прием пищи с указанным ID не найден'
            ], 404);
        }

        $meal->delete();

        return response()->json([
            'data' => null,
            'message' => 'Прием пищи успешно удален'
        ], 200);
    }

    /**
     * Отметка приема пищи как выполненного/невыполненного.
     */
    public function markMealAsCompleted(Request $request, $mealId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'completed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $validator->errors()
            ], 422);
        }

        $meal = Meal::where('id', $mealId)->where('user_id', $request->user()->id)->first();

        if (!$meal) {
            return response()->json([
                'error' => 'Ресурс не найден',
                'message' => 'Прием пищи с указанным ID не найден'
            ], 404);
        }

        $meal->completed = $validator->validated()['completed'];
        $meal->save();

        return response()->json([
            'data' => $this->formatMealResponse($meal),
            'message' => 'Статус выполнения приема пищи успешно обновлен'
        ]);
    }

    /**
     * Вспомогательный метод для форматирования ответа по приему пищи.
     */
    private function formatMealResponse(Meal $meal): array
    {
        return [
            'id' => $meal->id,
            'name' => $meal->name,
            'type' => $meal->type,
            'calories' => (float)$meal->calories,
            'proteins' => (float)$meal->proteins,
            'fats' => (float)$meal->fats,
            'carbs' => (float)$meal->carbs,
            'foodId' => $meal->food_id,
            'date' => $meal->date,
            'completed' => (bool)$meal->completed,
        ];
    }

    public function getDailyNutrition(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $validator->errors()
            ], 422);
        }

        $date = $request->input('date', now()->toDateString());
        $user = $request->user();

        $meals = Meal::where('user_id', $user->id)
            ->where('date', $date)
            ->orderBy('type')
            ->get();

        $totalCalories = 0;
        $totalProteins = 0;
        $totalCarbs = 0;
        $totalFats = 0;

        $mealsData = $meals->map(function ($meal) use (&$totalCalories, &$totalProteins, &$totalCarbs, &$totalFats) {
            $totalCalories += $meal->calories;
            $totalProteins += $meal->proteins;
            $totalCarbs += $meal->carbs;
            $totalFats += $meal->fats;
            return $this->formatMealResponse($meal);
        });

        $userFitnessGoal = $user->fitnessGoal;

        return response()->json([
            'data' => [
                'date' => $date,
                'meals' => $mealsData,
                'summary' => [
                    'totalCalories' => (float)$totalCalories,
                    'totalProteins' => (float)$totalProteins,
                    'totalCarbs' => (float)$totalCarbs,
                    'totalFats' => (float)$totalFats,
                ],
                'goals' => $userFitnessGoal ? [
                    'calories' => (float)$userFitnessGoal->target_calories,
                    'proteins' => (float)$userFitnessGoal->target_proteins,
                    'carbs' => (float)$userFitnessGoal->target_carbs,
                    'fats' => (float)$userFitnessGoal->target_fats,
                ] : null,
            ],
            'message' => 'Дневное питание успешно получено'
        ]);
    }
} 