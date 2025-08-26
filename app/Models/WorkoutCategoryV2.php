<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WorkoutGender;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель категории тренировок V2
 */
class WorkoutCategoryV2 extends BaseModel
{
    /**
     * Название таблицы
     */
    protected $table = 'workout_categories_v2';
    /**
     * Атрибуты, которые можно массово назначать
     */
    protected $fillable = [
        'name',
        'gender',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    /**
     * Атрибуты, которые должны быть приведены к нативным типам
     */
    protected $casts = [
        'gender' => WorkoutGender::class,
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Отношение к программам тренировок
     */
    public function workoutPrograms(): HasMany
    {
        return $this->hasMany(WorkoutProgramV2::class, 'category_id');
    }

    /**
     * Scope для активных категорий
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для категорий по полу
     */
    public function scopeByGender(Builder $query, WorkoutGender $gender): Builder
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope для сортировки по порядку
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Получить активные программы в категории
     */
    public function getActivePrograms()
    {
        return $this->workoutPrograms()->active()->ordered();
    }

    /**
     * Получить бесплатные программы в категории
     */
    public function getFreePrograms()
    {
        return $this->workoutPrograms()->active()->where('is_free', true)->ordered();
    }

    /**
     * Получить платные программы в категории
     */
    public function getPaidPrograms()
    {
        return $this->workoutPrograms()->active()->where('is_free', false)->ordered();
    }

    /**
     * Проверить, есть ли бесплатные программы
     */
    public function hasFreePrograms(): bool
    {
        return $this->workoutPrograms()->active()->where('is_free', true)->exists();
    }

    /**
     * Проверить, есть ли платные программы
     */
    public function hasPaidPrograms(): bool
    {
        return $this->workoutPrograms()->active()->where('is_free', false)->exists();
    }

    /**
     * Получить количество активных программ
     */
    public function getActiveProgramsCount(): int
    {
        return $this->workoutPrograms()->active()->count();
    }

    /**
     * Получить количество бесплатных программ
     */
    public function getFreeProgramsCount(): int
    {
        return $this->workoutPrograms()->active()->where('is_free', true)->count();
    }

    /**
     * Получить количество платных программ
     */
    public function getPaidProgramsCount(): int
    {
        return $this->workoutPrograms()->active()->where('is_free', false)->count();
    }
}
