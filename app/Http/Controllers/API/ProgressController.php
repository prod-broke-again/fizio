<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Progress;
use App\Models\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProgressController extends Controller
{
    private function formatProgressResponse(Progress $progress): array
    {
        return [
            'id' => $progress->id,
            'userId' => $progress->user_id,
            'date' => $progress->date->format('Y-m-d'),
            'calories' => $progress->calories,
            'steps' => $progress->steps,
            'workoutTime' => $progress->workout_time, // минуты
            'waterIntake' => $progress->water_intake, // литры
            'weight' => $progress->weight, // кг
            'bodyFatPercentage' => $progress->body_fat_percentage, // %
            'measurements' => $progress->measurements, // JSON: { chest, waist, hips, ... }
            'photos' => $progress->photos, // JSON: массив URL
            // 'dailyProgress' => $progress->calculateDailyProgress(), // Пока закомментировано
        ];
    }

    /**
     * Получение записи прогресса за конкретный день.
     * Используется вместо старого getDailyProgress, но теперь дата обязательна.
     */
    public function getProgressByDate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Ошибка валидации', 'messages' => $validator->errors()], 422);
        }

        $validatedDate = $validator->validated()['date'];
        $userId = Auth::id();

        $progress = Progress::where('user_id', $userId)
            ->where('date', $validatedDate)
            ->first();

        if (!$progress) {
            // Создаем "пустой" объект Progress, если запись не найдена
            $progress = new Progress([
                'user_id' => $userId,
                'date' => Carbon::parse($validatedDate), // Убедимся, что это Carbon instance
                'calories' => 0,
                'steps' => 0,
                'workout_time' => 0,
                'water_intake' => 0,
                'weight' => 0,
                'body_fat_percentage' => 0,
                'measurements' => null, // или [] в зависимости от formatProgressResponse
                'photos' => null,       // или []
            ]);
            // Так как это новый, не сохраненный объект, ему нужен ID для formatProgressResponse, если он там используется
            // Однако, formatProgressResponse может справиться и без id, если он не из БД
            // Если formatProgressResponse требует id, можно присвоить временный, например:
            // $progress->id = Str::uuid()->toString(); // Или оставить null, если formatProgressResponse это обработает
        }

        return response()->json(['data' => $this->formatProgressResponse($progress)]);
    }

    /**
     * Обновление или создание записи прогресса (включая дневной прогресс и измерения).
     * POST /api/progress/measurements (для добавления измерений)
     * PATCH /api/progress/{date} (для обновления дневных данных)
     * Этот метод будет обрабатывать и то, и другое, в зависимости от переданных полей.
     */
    public function storeOrUpdateProgress(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'calories' => 'nullable|integer|min:0',
            'steps' => 'nullable|integer|min:0',
            'workoutTime' => 'nullable|integer|min:0',
            'waterIntake' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'bodyFatPercentage' => 'nullable|numeric|min:0|max:100',
            'measurements' => 'nullable|array',
            'measurements.chest' => 'nullable|numeric|min:0',
            'measurements.waist' => 'nullable|numeric|min:0',
            'measurements.hips' => 'nullable|numeric|min:0',
            // Добавить другие измерения по необходимости
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|string|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Ошибка валидации', 'messages' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $userId = Auth::id();
        $date = $validatedData['date'];

        $progressData = [
            'calories' => $validatedData['calories'] ?? null,
            'steps' => $validatedData['steps'] ?? null,
            'workout_time' => $validatedData['workoutTime'] ?? null,
            'water_intake' => $validatedData['waterIntake'] ?? null,
            'weight' => $validatedData['weight'] ?? null,
            'body_fat_percentage' => $validatedData['bodyFatPercentage'] ?? null,
            'measurements' => $validatedData['measurements'] ?? null,
            'photos' => $validatedData['photos'] ?? null,
        ];

        // Удаляем null значения, чтобы они не перезаписывали существующие данные при частичном обновлении
        $updateData = array_filter($progressData, fn($value) => !is_null($value));

        if (empty($updateData)) {
            return response()->json(['error' => 'Нет данных для обновления', 'message' => 'Необходимо предоставить хотя бы одно поле для обновления.'], 400);
        }

        $progress = Progress::updateOrCreate(
            [
                'user_id' => $userId,
                'date' => $date,
            ],
            $updateData + ['user_id' => $userId, 'date' => $date] // Гарантируем, что user_id и date присутствуют
        );

        // Если это была новая запись, и у нее нет ID (UUID), генерируем его.
        // Это не должно происходить с HasUuids, но на всякий случай.
        if (!$progress->id) {
            $progress->id = Str::uuid()->toString();
            $progress->save(); 
        }
        
        return response()->json(['data' => $this->formatProgressResponse($progress->fresh()), 'message' => 'Прогресс успешно сохранен'], 200);
    }

    /**
     * Получение общего прогресса пользователя (например, за последнюю неделю/месяц).
     * GET /api/progress
     */
    public function getOverallProgress(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'period' => 'sometimes|string|in:week,month,all', // default 'week'
            'endDate' => 'sometimes|date_format:Y-m-d', // Конечная дата периода, по умолчанию сегодня
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Ошибка валидации', 'messages' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $period = $validated['period'] ?? 'week';
        $endDate = Carbon::parse($validated['endDate'] ?? now()->toDateString());

        $startDate = match ($period) {
            'month' => $endDate->copy()->subMonth()->startOfDay(),
            'all' => null, // или очень ранняя дата, или не фильтровать по начальной дате
            default => $endDate->copy()->subWeek()->startOfDay(), // 'week'
        };

        $query = Progress::where('user_id', Auth::id())
                         ->where('date', '<=', $endDate->toDateString());
        
        if ($startDate) {
             $query->where('date', '>=', $startDate->toDateString());
        }

        $progressRecords = $query->orderBy('date', 'desc')->get();

        if ($progressRecords->isEmpty()) {
            return response()->json(['message' => 'Записи о прогрессе за указанный период не найдены.', 'data' => []], 200);
        }

        return response()->json(['data' => $progressRecords->map(fn($p) => $this->formatProgressResponse($p))]);
    }

    /**
     * Добавление новой цели.
     * POST /api/progress/goals
     */
    public function addGoal(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50', // e.g., weight, body_fat, run_distance
            'targetValue' => 'required|numeric',
            'currentValue' => 'nullable|numeric',
            'startValue' => 'nullable|numeric',
            'unit' => 'required|string|max:20', // e.g., kg, %, km
            'targetDate' => 'nullable|date_format:Y-m-d',
            'status' => 'sometimes|string|in:active,completed,abandoned',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Ошибка валидации', 'messages' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        
        $goal = Goal::create([
            'id' => Str::uuid()->toString(),
            'user_id' => Auth::id(),
            'name' => $validatedData['name'],
            'type' => $validatedData['type'],
            'target_value' => $validatedData['targetValue'],
            'current_value' => $validatedData['currentValue'] ?? $validatedData['startValue'] ?? null,
            'start_value' => $validatedData['startValue'] ?? $validatedData['currentValue'] ?? null,
            'unit' => $validatedData['unit'],
            'target_date' => $validatedData['targetDate'] ?? null,
            'status' => $validatedData['status'] ?? 'active',
            'notes' => [], // Инициализируем пустым массивом
        ]);

        return response()->json(['data' => $goal, 'message' => 'Цель успешно добавлена'], 201);
    }

    /**
     * Обновление прогресса по существующей цели.
     * POST /api/progress/goals/{goalId}/progress
     */
    public function updateGoalProgress(Request $request, $goalId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'currentValue' => 'required|numeric',
            'date' => 'sometimes|date_format:Y-m-d', // Дата записи этого прогресса
            'noteText' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Ошибка валидации', 'messages' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $goal = Goal::where('id', $goalId)->where('user_id', Auth::id())->first();

        if (!$goal) {
            return response()->json(['error' => 'Ресурс не найден', 'message' => 'Цель с указанным ID не найдена'], 404);
        }

        $goal->current_value = $validatedData['currentValue'];
        
        $newNote = [
            'date' => $validatedData['date'] ?? now()->toDateString(),
            'value' => $validatedData['currentValue'],
            'note' => $validatedData['noteText'] ?? null,
        ];
        
        $notes = $goal->notes ?? [];
        $notes[] = $newNote;
        $goal->notes = $notes;

        // Опционально: обновить статус цели, если она достигнута
        if ($goal->target_value <= $goal->current_value && ($goal->type === 'weight' || $goal->type === 'body_fat')) { // Пример для целей на снижение
             // или $goal->current_value >= $goal->target_value для целей на увеличение
            // $goal->status = 'completed';
        } else if ($goal->current_value >= $goal->target_value) { // Общий случай для "достичь X"
            // $goal->status = 'completed';
        }

        $goal->save();

        return response()->json(['data' => $goal, 'message' => 'Прогресс по цели успешно обновлен']);
    }

    /**
     * Получение статистики прогресса (заглушка).
     * GET /api/progress/statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        // TODO: Реализовать логику сбора и форматирования статистики
        // Например, средние значения, тренды, сравнение с предыдущими периодами и т.д.
        $userId = Auth::id();
        return response()->json(['message' => 'Endpoint статистики находится в разработке.', 'data' => []]);
    }

    /**
     * Получение достижений пользователя (заглушка).
     * GET /api/progress/achievements
     */
    public function getAchievements(Request $request): JsonResponse
    {
        // TODO: Реализовать логику достижений
        // Например, "Достигнут вес X кг", "5 тренировок на этой неделе" и т.д.
        $userId = Auth::id();
        return response()->json(['message' => 'Endpoint достижений находится в разработке.', 'data' => []]);
    }
} 