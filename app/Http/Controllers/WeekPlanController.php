<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddMealRequest;
use App\Http\Requests\AddWorkoutRequest;
use App\Models\WeekPlan;
use App\Models\Workout;
use App\Models\Meal;
use App\Models\MealItem;
use App\Services\WeekPlanService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WeekPlanController extends Controller
{
    protected WeekPlanService $weekPlanService;

    public function __construct(WeekPlanService $weekPlanService)
    {
        $this->weekPlanService = $weekPlanService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Получаем часовой пояс из заголовка x-timezone, fallback на часовой пояс приложения
        $timezone = $request->header('x-timezone', config('app.timezone'));

        // Создаем объект Carbon для текущего момента в часовом поясе пользователя
        $nowInUserTimezone = Carbon::now($timezone);
        
        // Рассчитываем начало понедельника и конец воскресенья в часовом поясе пользователя
        $startOfWeek = $nowInUserTimezone->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $nowInUserTimezone->copy()->endOfWeek(Carbon::SUNDAY);
        
        $startOfWeekString = $startOfWeek->toDateString();
        $endOfWeekString = $endOfWeek->toDateString();

        // Получаем данные из новой системы meals
        $meals = Meal::where('user_id', $user->id)
            ->whereBetween('date', [$startOfWeekString, $endOfWeekString])
            ->with(['items.product'])
            ->orderBy('date')
            ->orderBy('type')
            ->get();

        // Группируем по дням
        $weekData = [];
        $currentDate = $startOfWeek->copy();
        
        while ($currentDate->lte($endOfWeek)) {
            $dateString = $currentDate->toDateString();
            // Сравниваем даты как Carbon объекты
            $dayMeals = $meals->filter(function($meal) use ($currentDate) {
                return $meal->date->equalTo($currentDate);
            });
            
            // Простое название дня недели без локализации
            $dayNames = ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'];
            $dayIndex = $currentDate->dayOfWeek; // Carbon::SUNDAY = 0, Carbon::MONDAY = 1, etc.
            $dayName = $dayNames[$dayIndex] ?? 'день';
            
            $weekData[] = [
                'date' => $dateString,
                'dayName' => $dayName,
                'meals' => $this->formatMealsForWeekPlan($dayMeals)
            ];
            
            $currentDate->addDay();
        }

        return response()->json($weekData);
    }

    public function show(Request $request, string $date): JsonResponse
    {
        $user = $request->user();

        // Получаем данные из новой системы meals
        $meals = Meal::where('user_id', $user->id)
            ->where('date', $date)
            ->with(['items.product'])
            ->orderBy('type')
            ->get();

        $dayData = [
            'date' => $date,
            'dayName' => $this->getDayName(Carbon::parse($date)),
            'meals' => $this->formatMealsForWeekPlan($meals)
        ];

        return response()->json($dayData);
    }

    public function addMeal(AddMealRequest $request, string $date): JsonResponse
    {
        // Этот метод теперь проксирует к новой системе meals
        // Создаем новый приём пищи через Meal модель
        $user = $request->user();
        
        $meal = Meal::create([
            'user_id' => $user->id,
            'date' => $date,
            'name' => $request->input('name', 'Приём пищи'),
            'type' => $request->input('type', 'breakfast'),
            'time' => $request->input('time'),
            'completed' => false,
            'calories' => 0,
            'proteins' => 0,
            'fats' => 0,
            'carbs' => 0
        ]);

        // Если есть элементы питания, добавляем их
        if ($request->has('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $itemData) {
                $meal->items()->create([
                    'product_id' => $itemData['product_id'] ?? null,
                    'free_text' => $itemData['free_text'] ?? null,
                    'grams' => $itemData['grams'] ?? null,
                    'servings' => $itemData['servings'] ?? null,
                    'calories' => $itemData['calories'] ?? 0,
                    'proteins' => $itemData['proteins'] ?? 0,
                    'fats' => $itemData['fats'] ?? 0,
                    'carbs' => $itemData['carbs'] ?? 0
                ]);
            }
            
            // Пересчитываем общие значения
            $this->recalculateMealTotals($meal);
        }

        return response()->json($this->formatMealsForWeekPlan(collect([$meal->fresh(['items.product'])])));
    }

    public function addWorkout(AddWorkoutRequest $request, string $date): JsonResponse
    {
        $user = $request->user();
        $plan = WeekPlan::firstOrCreate(
            ['user_id' => $user->id, 'date' => $date],
            ['meals' => [], 'workouts' => [], 'progress' => 0]
        );

        $plan = $this->weekPlanService->addWorkout($plan, $request->validated());
        return response()->json($this->formatPlanResponse($plan));
    }

    public function updateProgress(Request $request, string $date): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'progress' => 'required|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $progress = $validator->validated()['progress'];

        $plan = WeekPlan::where('user_id', $user->id)
            ->where('date', $date)
            ->firstOrFail();

        $plan->progress = $progress;
        $plan->save();

        return response()->json($this->formatPlanResponse($plan));
    }

    public function markAsCompleted(Request $request, string $date, string $type, int|string $id): JsonResponse
    {
        $user = $request->user();
        
        if ($type === 'meal') {
            // Обрабатываем приём пищи через новую систему
            $meal = Meal::where('user_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();
            
            $meal->update(['completed' => !$meal->completed]);
            
            return response()->json([
                'success' => true,
                'meal' => $this->formatMealForWeekPlan($meal)
            ]);
        } else {
            // Обрабатываем тренировки через старую систему
            $plan = WeekPlan::where('user_id', $user->id)
                ->where('date', $date)
                ->first();
            $plan = $this->weekPlanService->markAsCompleted($plan, $type, $id);

            return response()->json($this->formatPlanResponse($plan));
        }
    }

    /**
     * Форматирует приёмы пищи для week-plan API
     */
    private function formatMealsForWeekPlan($meals): array
    {
        return $meals->map(function ($meal) {
            return $this->formatMealForWeekPlan($meal);
        })->toArray();
    }

    /**
     * Форматирует один приём пищи для week-plan API
     */
    private function formatMealForWeekPlan(Meal $meal): array
    {
        $items = $meal->items->map(function ($item) {
            $itemData = [
                'id' => $item->id,
                'calories' => $item->calories,
                'proteins' => $item->proteins,
                'fats' => $item->fats,
                'carbs' => $item->carbs
            ];

            if ($item->product_id) {
                $itemData['product'] = [
                    'id' => $item->product->code,
                    'name' => $item->product->product_name,
                    'image' => $item->product->image_url
                ];
                $itemData['grams'] = $item->grams;
            } else {
                $itemData['free_text'] = $item->free_text;
                $itemData['grams'] = $item->grams;
            }

            if ($item->servings) {
                $itemData['servings'] = $item->servings;
            }

            return $itemData;
        })->toArray();

        return [
            'id' => $meal->id,
            'name' => $meal->name,
            'mealType' => $meal->type,
            'time' => $meal->time,
            'completed' => $meal->completed,
            'calories' => $meal->calories,
            'proteins' => $meal->proteins,
            'fats' => $meal->fats,
            'carbs' => $meal->carbs,
            'items' => $items
        ];
    }

    /**
     * Пересчитывает общие значения приёма пищи
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
     * Форматирует ответ с полной информацией о плане (для старых методов)
     */
    private function formatPlanResponse(WeekPlan $plan): array
    {
        $workouts = collect($plan->workouts)->map(function ($workoutEntry) {
            $workout = Workout::find($workoutEntry['workout_id']);
            if (!$workout) {
                return null;
            }

            return [
                'id' => $workoutEntry['id'],
                'workout_id' => $workoutEntry['workout_id'],
                'completed' => $workoutEntry['completed'],
                'name' => $workout->name,
                'type' => $workout->type,
                'exercises' => $workout->exercises,
                'duration' => $workout->duration,
                'difficulty' => $workout->difficulty,
                'image_url' => $workout->image_url,
                'calories_burned' => $workout->calories_burned,
            ];
        })->filter()->values()->toArray();

        return [
            'id' => $plan->id,
            'user_id' => $plan->user_id,
            'date' => $plan->date->format('Y-m-d'),
            'meals' => $plan->meals,
            'workouts' => $workouts,
            'progress' => $plan->progress,
            'created_at' => $plan->created_at,
            'updated_at' => $plan->updated_at,
        ];
    }

    /**
     * Получает простое название дня недели
     */
    private function getDayName(Carbon $date): string
    {
        $dayNames = ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'];
        return $dayNames[$date->dayOfWeek] ?? 'день';
    }
}
