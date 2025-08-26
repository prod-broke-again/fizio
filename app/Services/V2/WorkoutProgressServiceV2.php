<?php

declare(strict_types=1);

namespace App\Services\V2;

use App\Models\User;
use App\Models\UserWorkoutProgressV2;
use App\Models\WorkoutProgramV2;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class WorkoutProgressServiceV2
{
    /**
     * Получить прогресс пользователя
     */
    public function getUserProgress(int $userId): Collection
    {
        return UserWorkoutProgressV2::where('user_id', $userId)
            ->with(['program.category', 'exercise'])
            ->orderBy('completed_at', 'desc')
            ->get();
    }
    
    /**
     * Сохранить прогресс тренировки
     */
    public function storeProgress(int $userId, array $data): UserWorkoutProgressV2
    {
        return UserWorkoutProgressV2::create([
            'user_id' => $userId,
            'program_id' => $data['program_id'],
            'exercise_id' => $data['exercise_id'],
            'completed_at' => $data['completed_at'] ?? now(),
            'duration_seconds' => $data['duration_seconds'] ?? 0,
            'notes' => $data['notes'] ?? null,
        ]);
    }
    
    /**
     * Обновить прогресс тренировки
     */
    public function updateProgress(UserWorkoutProgressV2 $progress, array $data): UserWorkoutProgressV2
    {
        $progress->update($data);
        return $progress->fresh();
    }
    
    /**
     * Получить статистику пользователя
     */
    public function getUserStatistics(int $userId): array
    {
        $totalWorkouts = UserWorkoutProgressV2::where('user_id', $userId)->count();
        $totalDuration = UserWorkoutProgressV2::where('user_id', $userId)->sum('duration_seconds');
        $uniquePrograms = UserWorkoutProgressV2::where('user_id', $userId)
            ->distinct('program_id')
            ->count('program_id');
        
        // Статистика по дням недели
        $weeklyStats = $this->getWeeklyStats($userId);
        
        // Статистика по программам
        $programStats = $this->getProgramStats($userId);
        
        // Текущая серия тренировок
        $currentStreak = $this->getCurrentStreak($userId);
        
        return [
            'total_workouts' => $totalWorkouts,
            'total_duration_minutes' => round($totalDuration / 60, 2),
            'unique_programs' => $uniquePrograms,
            'average_duration_minutes' => $totalWorkouts > 0 ? round($totalDuration / 60 / $totalWorkouts, 2) : 0,
            'weekly_stats' => $weeklyStats,
            'program_stats' => $programStats,
            'current_streak' => $currentStreak,
        ];
    }
    
    /**
     * Получить статистику по дням недели
     */
    private function getWeeklyStats(int $userId): array
    {
        $stats = UserWorkoutProgressV2::where('user_id', $userId)
            ->selectRaw('DAYOFWEEK(completed_at) as day_of_week, COUNT(*) as count')
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week');
        
        $weekDays = [
            1 => 'Воскресенье',
            2 => 'Понедельник',
            3 => 'Вторник',
            4 => 'Среда',
            5 => 'Четверг',
            6 => 'Пятница',
            7 => 'Суббота',
        ];
        
        $result = [];
        foreach ($weekDays as $dayNum => $dayName) {
            $result[$dayName] = $stats->get($dayNum)?->count ?? 0;
        }
        
        return $result;
    }
    
    /**
     * Получить статистику по программам
     */
    private function getProgramStats(int $userId): array
    {
        return UserWorkoutProgressV2::where('user_id', $userId)
            ->with('program.category')
            ->selectRaw('program_id, COUNT(*) as workout_count, SUM(duration_seconds) as total_duration')
            ->groupBy('program_id')
            ->orderBy('workout_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($stat) {
                return [
                    'program_name' => $stat->program->name,
                    'category_name' => $stat->program->category->name,
                    'workout_count' => $stat->workout_count,
                    'total_duration_minutes' => round($stat->total_duration / 60, 2),
                ];
            })
            ->toArray();
    }
    
    /**
     * Получить текущую серию тренировок
     */
    private function getCurrentStreak(int $userId): int
    {
        $workouts = UserWorkoutProgressV2::where('user_id', $userId)
            ->orderBy('completed_at', 'desc')
            ->get()
            ->groupBy(function ($workout) {
                return $workout->completed_at->format('Y-m-d');
            });
        
        $streak = 0;
        $currentDate = now();
        
        foreach ($workouts as $date => $dayWorkouts) {
            $workoutDate = Carbon::parse($date);
            $diffDays = $currentDate->diffInDays($workoutDate, false);
            
            if ($diffDays <= 1 && count($dayWorkouts) > 0) {
                $streak++;
                $currentDate = $workoutDate;
            } else {
                break;
            }
        }
        
        return $streak;
    }
    
    /**
     * Получить прогресс по конкретной программе
     */
    public function getProgramProgress(int $userId, int $programId): array
    {
        $totalExercises = WorkoutProgramV2::find($programId)->workoutExercises()->count();
        $completedExercises = UserWorkoutProgressV2::where('user_id', $userId)
            ->where('program_id', $programId)
            ->distinct('exercise_id')
            ->count('exercise_id');
        
        $progressPercentage = $totalExercises > 0 ? round(($completedExercises / $totalExercises) * 100, 2) : 0;
        
        return [
            'total_exercises' => $totalExercises,
            'completed_exercises' => $completedExercises,
            'progress_percentage' => $progressPercentage,
            'remaining_exercises' => $totalExercises - $completedExercises,
        ];
    }
}
