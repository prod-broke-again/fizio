<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Workout;
use App\Models\WorkoutProgress;
use App\Models\WeekPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WorkoutController extends Controller
{
    /**
     * Получение плана тренировок пользователя (например, на текущую неделю).
     * Использует WeekPlan для определения расписания и статуса тренировок.
     */
    public function getWorkoutPlan(Request $request)
    {
        \Log::info('WorkoutController@getWorkoutPlan called', [
            'user_id' => $request->user()->id,
            'request' => $request->all()
        ]);

        $user = $request->user();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();

        \Log::info('Date range', [
            'start' => $startOfWeek,
            'end' => $endOfWeek
        ]);

        $weekPlans = WeekPlan::where(function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereNull('user_id');
        })
                            ->whereBetween('date', [$startOfWeek, $endOfWeek])
                            ->orderBy('date')
                            ->get();

        \Log::info('Week plans found', [
            'count' => $weekPlans->count()
        ]);

        $responseWorkouts = [];

        foreach ($weekPlans as $plan) {
            // Убедимся, что дата плана это строка в формате YYYY-MM-DD
            $planDateStr = ($plan->date instanceof \Carbon\Carbon) ? $plan->date->toDateString() : (string) $plan->date;

            $workoutEntries = is_array($plan->workouts) ? $plan->workouts : [];

            foreach ($workoutEntries as $workoutEntry) {
                if (!isset($workoutEntry['workout_id'])) {
                    continue;
                }
                $workoutId = $workoutEntry['workout_id'];
                $isCompletedInPlan = (bool)($workoutEntry['completed'] ?? false);

                $workoutItem = Workout::find($workoutId);

                if ($workoutItem) {
                    $responseWorkouts[] = $this->formatWorkoutForPlanResponse(
                        $workoutItem,
                        $planDateStr,
                        $isCompletedInPlan
                    );
                }
            }
        }

        return response()->json([
            'data' => $responseWorkouts,
            'message' => 'План тренировок успешно получен'
        ]);
    }

    /**
     * Добавление новой тренировки.
     */
    public function addWorkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:strength,cardio,hiit,flexibility',
            'exercises' => 'required|array',
            'exercises.*.exercise_id' => 'required|string',
            'exercises.*.sets' => 'required|array',
            'exercises.*.sets.*.weight' => 'nullable|numeric|min:0',
            'exercises.*.sets.*.reps' => 'nullable|integer|min:0',
            'exercises.*.sets.*.duration' => 'nullable|integer|min:0',
            'exercises.*.sets.*.distance' => 'nullable|numeric|min:0',
            'exercises.*.order' => 'required|integer|min:0',
            'exercises.*.rest_time' => 'required|integer|min:0',
            'duration' => 'required|integer|min:1',
            'difficulty' => 'required|string|in:beginner,intermediate,advanced',
            'date' => 'required|date_format:Y-m-d',
            'image_url' => 'nullable|string|url|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Ошибка валидации', 'messages' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $user = $request->user();

        $workout = Workout::create([
            'id' => Str::uuid()->toString(),
            'user_id' => $user->id,
            'name' => trim($validatedData['name']),
            'type' => $validatedData['type'],
            'exercises' => $validatedData['exercises'],
            'duration' => $validatedData['duration'],
            'difficulty' => $validatedData['difficulty'],
            'date' => $validatedData['date'],
            'completed' => false,
            'calories_burned' => null,
            'image_url' => $validatedData['image_url'] ?? null,
        ]);

        return response()->json(['data' => $this->formatWorkoutResponse($workout), 'message' => 'Тренировка успешно добавлена'], 201);
    }

    /**
     * Обновление существующей тренировки.
     */
    public function updateWorkout(Request $request, $workoutId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|in:strength,cardio,hiit,flexibility',
            'exercises' => 'sometimes|required|array',
            'exercises.*.exercise_id' => 'sometimes|required|string',
            'exercises.*.sets' => 'sometimes|required|array',
            'exercises.*.sets.*.weight' => 'nullable|numeric|min:0',
            'exercises.*.sets.*.reps' => 'nullable|integer|min:0',
            'exercises.*.sets.*.duration' => 'nullable|integer|min:0',
            'exercises.*.sets.*.distance' => 'nullable|numeric|min:0',
            'exercises.*.order' => 'sometimes|required|integer|min:0',
            'exercises.*.rest_time' => 'sometimes|required|integer|min:0',
            'duration' => 'sometimes|required|integer|min:1',
            'difficulty' => 'sometimes|required|string|in:beginner,intermediate,advanced',
            'date' => 'sometimes|required|date_format:Y-m-d',
            'image_url' => 'nullable|string|url|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Ошибка валидации', 'messages' => $validator->errors()], 422);
        }

        $workout = Workout::where('id', $workoutId)->where('user_id', $request->user()->id)->first();

        if (!$workout) {
            return response()->json(['error' => 'Ресурс не найден', 'message' => 'Тренировка с указанным ID не найдена'], 404);
        }

        $validatedData = $validator->validated();
        if (isset($validatedData['name'])) {
            $validatedData['name'] = trim($validatedData['name']);
        }

        $workout->update($validatedData);

        return response()->json(['data' => $this->formatWorkoutResponse($workout->fresh()), 'message' => 'Тренировка успешно обновлена']);
    }

    /**
     * Отметка тренировки как выполненной/невыполненной.
     */
    public function markWorkoutAsCompleted(Request $request, $workoutId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'completed' => 'required|boolean',
            'calories_burned' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Ошибка валидации', 'messages' => $validator->errors()], 422);
        }

        $workout = Workout::where('id', $workoutId)->where('user_id', $request->user()->id)->first();

        if (!$workout) {
            return response()->json(['error' => 'Ресурс не найден', 'message' => 'Тренировка с указанным ID не найдена'], 404);
        }

        $validatedData = $validator->validated();
        $workout->completed = $validatedData['completed'];
        if (isset($validatedData['calories_burned'])) {
            $workout->calories_burned = $validatedData['calories_burned'];
        }
        $workout->save();

        return response()->json(['data' => $this->formatWorkoutResponse($workout), 'message' => 'Статус выполнения тренировки успешно обновлен']);
    }

    /**
     * Вспомогательный метод для форматирования ответа по тренировке.
     */
    private function formatWorkoutResponse(Workout $workout): array
    {
        $imageUrl = $workout->image_url;
        if (empty($imageUrl)) {
            $text = urlencode($workout->name); // Кодируем имя тренировки для URL
            // Генерируем URL для заглушки. Размеры 600x400, серый фон, темный текст.
            $imageUrl = "https://dummyimage.com/600x400/dee2e6/6c757d.png&text=" . $text;
        }

        return [
            'id' => $workout->id,
            'name' => $workout->name,
            'type' => $workout->type,
            'exercises' => $workout->exercises,
            'duration' => $workout->duration,
            'difficulty' => $workout->difficulty,
            'date' => $workout->date,
            'completed' => (bool)$workout->completed,
            'caloriesBurned' => $workout->calories_burned,
            'imageUrl' => $imageUrl, // Используем оригинальный или сгенерированный URL
        ];
    }

    /**
     * Получение расписания тренировок
     *
     * @return JsonResponse
     */
    public function getSchedule()
    {
        $user = Auth::user();
        $workouts = Workout::where('user_id', $user->id)
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $workouts
        ]);
    }

    /**
     * Добавление новой тренировки
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'duration' => 'required|integer|min:1',
            'calories' => 'required|integer|min:0',
            'image_url' => 'nullable|string|url'
        ]);

        $user = Auth::user();

        $workout = new Workout([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'duration' => $request->duration,
            'calories' => $request->calories,
            'image_url' => $request->image_url
        ]);

        $workout->save();

        return response()->json([
            'success' => true,
            'data' => $workout
        ]);
    }

    /**
     * Обновление тренировки
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
            'duration' => 'nullable|integer|min:1',
            'calories' => 'nullable|integer|min:0',
            'image_url' => 'nullable|string|url'
        ]);

        $user = Auth::user();
        $workout = Workout::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$workout) {
            return response()->json([
                'success' => false,
                'message' => 'Тренировка не найдена'
            ], 404);
        }

        // Обновляем только предоставленные поля
        if ($request->has('title')) {
            $workout->title = $request->title;
        }

        if ($request->has('description')) {
            $workout->description = $request->description;
        }

        if ($request->has('date')) {
            $workout->date = $request->date;
        }

        if ($request->has('duration')) {
            $workout->duration = $request->duration;
        }

        if ($request->has('calories')) {
            $workout->calories = $request->calories;
        }

        if ($request->has('image_url')) {
            $workout->image_url = $request->image_url;
        }

        $workout->save();

        return response()->json([
            'success' => true,
            'data' => $workout
        ]);
    }

    /**
     * Удаление тренировки
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $workout = Workout::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$workout) {
            return response()->json([
                'success' => false,
                'message' => 'Тренировка не найдена'
            ], 404);
        }

        $workout->delete();

        return response()->json([
            'success' => true
        ]);
    }

    public function index(Request $request)
    {
        \Log::info('WorkoutController@index called', [
            'user_id' => $request->user()->id,
            'request' => $request->all()
        ]);

        $user = $request->user();
        $query = Workout::where(function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereNull('user_id');
        });

        $perPage = min($request->get('per_page', 20), 50);
        $workouts = $query->orderBy('date', 'desc')->paginate($perPage);

        \Log::info('Workouts found', [
            'count' => $workouts->count(),
            'total' => $workouts->total()
        ]);

        $workouts->getCollection()->transform(fn($workout) => $this->formatWorkoutResponse($workout));

        return response()->json($workouts);
    }

    public function show(Request $request, $workoutId)
    {
        $workout = Workout::where('id', $workoutId)->where('user_id', $request->user()->id)->first();
        if (!$workout) {
            return response()->json(['error' => 'Ресурс не найден', 'message' => 'Тренировка с указанным ID не найдена'], 404);
        }
        return response()->json(['data' => $this->formatWorkoutResponse($workout), 'message' => 'Тренировка успешно получена']);
    }

    public function recommended()
    {
        // Здесь можно реализовать логику рекомендаций на основе целей пользователя,
        // уровня подготовки и истории тренировок
        $workouts = Workout::inRandomOrder()->limit(5)->get();

        $workouts->transform(function ($workout) {
            $workout->isFavorite = $workout->isFavorite(Auth::user());
            return $workout;
        });

        return response()->json($workouts);
    }

    public function toggleFavorite(Workout $workout)
    {
        $user = Auth::user();

        if ($workout->isFavorite($user)) {
            $workout->users()->detach($user->id);
            $message = 'Тренировка удалена из избранного';
        } else {
            $workout->users()->attach($user->id);
            $message = 'Тренировка добавлена в избранное';
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    public function logWorkout(Request $request)
    {
        $validated = $request->validate([
            'workout_id' => 'required|exists:workouts,id',
            'duration' => 'required|integer|min:1',
            'calories_burned' => 'nullable|integer|min:0',
            'completed_exercises' => 'nullable|array',
            'completed_exercises.*.exercise_id' => 'required|exists:exercises,id',
            'completed_exercises.*.sets_completed' => 'nullable|integer|min:0',
            'completed_exercises.*.repetitions_completed' => 'nullable|integer|min:0',
            'completed_exercises.*.weight_used' => 'nullable|numeric|min:0',
            'completed_at' => 'required|date',
        ]);

        $progress = WorkoutProgress::create([
            'user_id' => Auth::id(),
            'workout_id' => $validated['workout_id'],
            'duration' => $validated['duration'],
            'calories_burned' => $validated['calories_burned'] ?? null,
            'completed_at' => $validated['completed_at'],
        ]);

        // Здесь можно добавить логику сохранения данных о выполненных упражнениях

        return response()->json([
            'success' => true,
            'message' => 'Данные тренировки успешно записаны',
            'data' => $progress
        ]);
    }

    /**
     * Вспомогательный метод для форматирования ответа по тренировке в контексте плана.
     */
    private function formatWorkoutForPlanResponse(Workout $workoutItem, string $planDate, bool $isCompletedInPlan): array
    {
        $imageUrl = $workoutItem->image_url;
        if (empty($imageUrl)) {
            $text = urlencode($workoutItem->name); // Кодируем имя тренировки для URL
            // Генерируем URL для заглушки. Размеры 600x400, серый фон, темный текст.
            $imageUrl = "https://dummyimage.com/600x400/dee2e6/6c757d.png&text=" . $text;
        }

        return [
            'id' => $workoutItem->id,
            'name' => $workoutItem->name,
            'type' => $workoutItem->type,
            'exercises' => $workoutItem->exercises, // Предполагается, что это уже массив/объект или корректно приводится к JSON
            'duration' => $workoutItem->duration,
            'difficulty' => $workoutItem->difficulty,
            'date' => $planDate, // Используем дату из плана
            'completed' => $isCompletedInPlan, // Используем статус выполнения из плана
            'caloriesBurned' => $isCompletedInPlan ? $workoutItem->calories_burned : null, // Калории только если выполнено
            'imageUrl' => $imageUrl,
        ];
    }
}
