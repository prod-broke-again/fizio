<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WorkoutDifficulty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель программы тренировок V2
 */
class WorkoutProgramV2 extends BaseModel
{
    /**
     * Название таблицы
     */
    protected $table = 'workout_programs_v2';
    
    /**
     * Атрибуты, которые можно массово назначать
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'difficulty_level',
        'duration_weeks',
        'calories_per_workout',
        'video_url',
        'thumbnail_url',
        'video_file',
        'is_free',
        'is_active',
        'sort_order',
    ];

    /**
     * Атрибуты, которые должны быть приведены к нативным типам
     */
    protected $casts = [
        'difficulty_level' => WorkoutDifficulty::class,
        'duration_weeks' => 'integer',
        'calories_per_workout' => 'integer',
        'is_free' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Отношение к категории
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(WorkoutCategoryV2::class, 'category_id');
    }

    /**
     * Отношение к упражнениям
     */
    public function exercises(): HasMany
    {
        return $this->hasMany(WorkoutExerciseV2::class, 'program_id');
    }

    /**
     * Алиас для отношения к упражнениям (для совместимости с Filament)
     */
    public function workout_exercises(): HasMany
    {
        return $this->exercises();
    }

    /**
     * Scope для активных программ
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для бесплатных программ
     */
    public function scopeFree(Builder $query): Builder
    {
        return $query->where('is_free', true);
    }

    /**
     * Scope для платных программ
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('is_free', false);
    }

    /**
     * Scope для программ по сложности
     */
    public function scopeByDifficulty(Builder $query, WorkoutDifficulty $difficulty): Builder
    {
        return $query->where('difficulty_level', $difficulty);
    }

    /**
     * Scope для сортировки по порядку
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Получить активные упражнения в программе
     */
    public function getActiveExercises()
    {
        return $this->exercises()->ordered();
    }

    /**
     * Получить количество упражнений
     */
    public function getExercisesCount(): int
    {
        return $this->exercises()->count();
    }

    /**
     * Получить общую длительность программы в секундах
     */
    public function getTotalDurationSeconds(): int
    {
        return (int) $this->exercises()->sum('duration_seconds');
    }

    /**
     * Получить общую длительность программы в минутах
     */
    public function getTotalDurationMinutes(): int
    {
        return (int) round($this->getTotalDurationSeconds() / 60);
    }

    /**
     * Проверить, есть ли видео
     */
    public function hasVideo(): bool
    {
        return !empty($this->video_url) || !empty($this->video_file);
    }

    /**
     * Проверить, есть ли превью
     */
    public function hasThumbnail(): bool
    {
        return !empty($this->thumbnail_url) || !empty($this->thumbnail_file);
    }

    /**
     * Получить URL видео (приоритет файлу)
     */
    public function getVideoUrl(): ?string
    {
        if (!empty($this->video_file)) {
            return asset('storage/' . $this->video_file);
        }
        
        return $this->video_url;
    }

    /**
     * Получить URL превью (приоритет файлу)
     */
    public function getThumbnailUrl(): ?string
    {
        if (!empty($this->thumbnail_file)) {
            return asset('storage/' . $this->thumbnail_file);
        }
        
        return $this->thumbnail_url;
    }

    /**
     * Получить общую длительность программы в часах
     */
    public function getTotalDurationHours(): float
    {
        return round($this->getTotalDurationSeconds() / 3600, 1);
    }

    /**
     * Проверить, является ли программа бесплатной
     */
    public function isFree(): bool
    {
        return $this->is_free;
    }

    /**
     * Проверить, является ли программа платной
     */
    public function isPaid(): bool
    {
        return !$this->is_free;
    }

    /**
     * Получить пол программы через категорию
     */
    public function getGender()
    {
        return $this->category?->gender;
    }

    /**
     * Получить название категории
     */
    public function getCategoryName(): ?string
    {
        return $this->category?->name;
    }
}
