<?php

declare(strict_types=1);

namespace App\Services\V2;

use App\Enums\WorkoutDifficulty;
use App\Enums\WorkoutGender;
use App\Models\WorkoutProgramV2;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class WorkoutServiceV2
{
    /**
     * Получить отфильтрованные программы тренировок
     */
    public function getFilteredPrograms(array $filters = []): Collection
    {
        $query = WorkoutProgramV2::query()
            ->where('is_active', true)
            ->with(['category', 'workoutExercises' => function ($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order');
        
        // Фильтр по категории
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        
        // Фильтр по уровню сложности
        if (isset($filters['difficulty_level'])) {
            $query->where('difficulty_level', $filters['difficulty_level']);
        }
        
        // Фильтр по бесплатности
        if (isset($filters['is_free'])) {
            $query->where('is_free', $filters['is_free']);
        }
        
        // Фильтр по полу
        if (isset($filters['gender'])) {
            $query->whereHas('category', function ($query) use ($filters) {
                $query->where('gender', $filters['gender']);
            });
        }
        
        return $query->get();
    }
    
    /**
     * Получить программы по полу пользователя
     */
    public function getProgramsByUserGender(User $user): Collection
    {
        $gender = $user->gender ?? WorkoutGender::MALE;
        
        return $this->getFilteredPrograms(['gender' => $gender->value]);
    }
    
    /**
     * Получить бесплатные программы
     */
    public function getFreePrograms(): Collection
    {
        return $this->getFilteredPrograms(['is_free' => true]);
    }
    
    /**
     * Получить программы по уровню сложности
     */
    public function getProgramsByDifficulty(WorkoutDifficulty $difficulty): Collection
    {
        return $this->getFilteredPrograms(['difficulty_level' => $difficulty->value]);
    }
    
    /**
     * Проверить доступ пользователя к программе
     */
    public function canUserAccessProgram(User $user, WorkoutProgramV2 $program): bool
    {
        // Бесплатные программы доступны всем
        if ($program->is_free) {
            return true;
        }
        
        // Проверяем активную подписку
        return $user->subscriptionV2()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();
    }
    
    /**
     * Получить рекомендуемые программы для пользователя
     */
    public function getRecommendedPrograms(User $user, int $limit = 5): Collection
    {
        $userGender = $user->gender ?? WorkoutGender::MALE;
        
        return WorkoutProgramV2::where('is_active', true)
            ->whereHas('category', function ($query) use ($userGender) {
                $query->where('gender', $userGender->value);
            })
            ->where('is_free', true) // Начинаем с бесплатных
            ->orderBy('sort_order')
            ->limit($limit)
            ->with(['category'])
            ->get();
    }
    
    /**
     * Очистить кеш программ
     */
    public function clearCache(): void
    {
        Cache::forget('workout_programs_v2_all');
        Cache::forget('workout_programs_v2_free');
        
        // Очищаем кеш по категориям
        Cache::forget('workout_programs_by_category_male');
        Cache::forget('workout_programs_by_category_female');
    }
}
