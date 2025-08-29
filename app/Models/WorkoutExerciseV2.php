<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель упражнения V2
 */
class WorkoutExerciseV2 extends BaseModel
{
    /**
     * Название таблицы
     */
    protected $table = 'workout_exercises_v2';
    
    /**
     * Атрибуты, которые можно массово назначать
     */
    protected $fillable = [
        'program_id',
        'name',
        'slug',
        'description',
        'instructions',
        'video_url',
        'thumbnail_url',
        'duration_seconds',
        'sets',
        'reps',
        'rest_seconds',
        'weight_kg',
        'equipment_needed',
        'muscle_groups',
        'sort_order',
    ];

    /**
     * Атрибуты, которые должны быть приведены к нативным типам
     */
    protected $casts = [
        'duration_seconds' => 'integer',
        'sets' => 'integer',
        'reps' => 'integer',
        'rest_seconds' => 'integer',
        'equipment_needed' => 'array',
        'muscle_groups' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Отношение к программе тренировок
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(WorkoutProgramV2::class, 'program_id');
    }

    /**
     * Scope для сортировки по порядку
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
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
     * Получить общее время выполнения (включая отдых)
     */
    public function getTotalTimeSeconds(): int
    {
        $exerciseTime = $this->duration_seconds * $this->sets;
        $restTime = $this->rest_seconds * ($this->sets - 1);
        
        return $exerciseTime + $restTime;
    }

    /**
     * Получить общее время выполнения в минутах
     */
    public function getTotalTimeMinutes(): int
    {
        return (int) round($this->getTotalTimeSeconds() / 60);
    }

    /**
     * Получить общее время выполнения в часах
     */
    public function getTotalTimeHours(): float
    {
        return round($this->getTotalTimeSeconds() / 3600, 2);
    }

    /**
     * Получить общее количество повторений
     */
    public function getTotalReps(): int
    {
        return $this->sets * $this->reps;
    }

    /**
     * Проверить, есть ли видео
     */
    public function hasVideo(): bool
    {
        return !empty($this->video_url);
    }

    /**
     * Проверить, есть ли превью
     */
    public function hasThumbnail(): bool
    {
        return !empty($this->thumbnail_url);
    }

    /**
     * Получить список оборудования как строку
     */
    public function getEquipmentList(): string
    {
        if (empty($this->equipment_needed)) {
            return 'Без оборудования';
        }
        
        return implode(', ', $this->equipment_needed);
    }

    /**
     * Получить список групп мышц как строку
     */
    public function getMuscleGroupsList(): string
    {
        if (empty($this->muscle_groups)) {
            return 'Не указано';
        }
        
        return implode(', ', $this->muscle_groups);
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
}
