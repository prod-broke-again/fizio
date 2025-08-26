<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель прогресса пользователя V2
 */
class UserWorkoutProgressV2 extends BaseModel
{
    /**
     * Название таблицы
     */
    protected $table = 'user_workout_progress_v2';
    
    /**
     * Атрибуты, которые можно массово назначать
     */
    protected $fillable = [
        'user_id',
        'program_id',
        'exercise_id',
        'completed_at',
        'duration_seconds',
        'notes',
    ];

    /**
     * Атрибуты, которые должны быть приведены к нативным типам
     */
    protected $casts = [
        'completed_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    /**
     * Отношение к пользователю
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Отношение к программе тренировок
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(WorkoutProgramV2::class, 'program_id');
    }

    /**
     * Отношение к упражнению
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(WorkoutExerciseV2::class, 'exercise_id');
    }

    /**
     * Scope для прогресса пользователя
     */
    public function scopeByUser(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope для прогресса по программе
     */
    public function scopeByProgram(Builder $query, string $programId): Builder
    {
        return $query->where('program_id', $programId);
    }

    /**
     * Scope для прогресса по упражнению
     */
    public function scopeByExercise(Builder $query, string $exerciseId): Builder
    {
        return $query->where('exercise_id', $exerciseId);
    }

    /**
     * Scope для прогресса за период
     */
    public function scopeByPeriod(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('completed_at', [$startDate, $endDate]);
    }

    /**
     * Scope для прогресса за сегодня
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('completed_at', today());
    }

    /**
     * Scope для прогресса за неделю
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('completed_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope для прогресса за месяц
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('completed_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    /**
     * Scope для сортировки по дате завершения
     */
    public function scopeOrderedByCompletion(Builder $query): Builder
    {
        return $query->orderBy('completed_at', 'desc');
    }

    /**
     * Получить длительность в минутах
     */
    public function getDurationMinutes(): int
    {
        return (int) round($this->duration_seconds / 60);
    }

    /**
     * Получить длительность в часах
     */
    public function getDurationHours(): float
    {
        return round($this->duration_seconds / 3600, 2);
    }

    /**
     * Получить название упражнения
     */
    public function getExerciseName(): ?string
    {
        return $this->exercise?->name;
    }

    /**
     * Получить название программы
     */
    public function getProgramName(): ?string
    {
        return $this->program?->name;
    }

    /**
     * Получить название категории
     */
    public function getCategoryName(): ?string
    {
        return $this->program?->category?->name;
    }

    /**
     * Получить пол через программу и категорию
     */
    public function getGender()
    {
        return $this->program?->category?->gender;
    }

    /**
     * Получить email пользователя
     */
    public function getUserEmail(): ?string
    {
        return $this->user?->email;
    }

    /**
     * Получить имя пользователя
     */
    public function getUserName(): ?string
    {
        return $this->user?->name;
    }

    /**
     * Проверить, завершено ли упражнение сегодня
     */
    public function isCompletedToday(): bool
    {
        return $this->completed_at->isToday();
    }

    /**
     * Проверить, завершено ли упражнение на этой неделе
     */
    public function isCompletedThisWeek(): bool
    {
        return $this->completed_at->isCurrentWeek();
    }

    /**
     * Проверить, завершено ли упражнение в этом месяце
     */
    public function isCompletedThisMonth(): bool
    {
        return $this->completed_at->isCurrentMonth();
    }

    /**
     * Получить время с момента завершения
     */
    public function getTimeSinceCompletion(): string
    {
        return $this->completed_at->diffForHumans();
    }

    /**
     * Получить время с момента завершения в днях
     */
    public function getDaysSinceCompletion(): int
    {
        return $this->completed_at->diffInDays(now());
    }
}
