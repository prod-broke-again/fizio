<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddMealRequest;
use App\Http\Requests\AddWorkoutRequest;
use App\Models\WeekPlan;
use App\Models\Workout;
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
        // Приводим к строковому формату YYYY-MM-DD для сравнения только по дате в БД
        $startOfWeekString = $nowInUserTimezone->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $endOfWeekString = $nowInUserTimezone->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
        $plans = WeekPlan::where('user_id', $user->id)
            // Сравниваем поле date (DATE тип в БД) со строками YYYY-MM-DD
            ->whereBetween('date', [$startOfWeekString, $endOfWeekString])
            ->orderBy('date')
            ->get();


        // dd($timezone, $nowInUserTimezone, $startOfWeekString, $endOfWeekString, $plans);

        $formattedPlans = $plans->map(function ($plan) {
            // Здесь при форматировании в JSON Carbon все равно может добавить время и Z
            return $this->formatPlanResponse($plan);
        });

        return response()->json($formattedPlans);
    }

    public function show(Request $request, string $date): JsonResponse
    {
        $user = $request->user();

        $plan = WeekPlan::where('user_id', $user->id)
            ->where('date', $date)
            ->firstOrFail();

        return response()->json($this->formatPlanResponse($plan));
    }

    public function addMeal(AddMealRequest $request, string $date): JsonResponse
    {
        $user = $request->user();
        $plan = WeekPlan::firstOrCreate(
            ['user_id' => $user->id, 'date' => $date],
            ['meals' => [], 'workouts' => [], 'progress' => 0]
        );

        $plan = $this->weekPlanService->addMeal($plan, $request->validated());
        return response()->json($this->formatPlanResponse($plan));
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
        $plan = WeekPlan::where('user_id', $user->id)
            ->where('date', $date)
            ->first();
        $plan = $this->weekPlanService->markAsCompleted($plan, $type, $id);

        return response()->json($this->formatPlanResponse($plan));
    }

    /**
     * Форматирует ответ с полной информацией о плане
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
}
