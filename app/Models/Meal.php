<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Meal extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'name',
        'type',
        'time',
        'calories',
        'proteins',
        'fats',
        'carbs',
        'food_id',
        'date',
        'completed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date:Y-m-d',
        'completed' => 'boolean',
        'calories' => 'float',
        'proteins' => 'float',
        'fats' => 'float',
        'carbs' => 'float',
        'time' => 'string',
    ];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с элементами приёма пищи.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MealItem::class);
    }

    /**
     * Получить общее количество калорий из всех элементов.
     */
    public function getTotalCaloriesAttribute(): float
    {
        return $this->items->sum('calories');
    }

    /**
     * Получить общее количество белков из всех элементов.
     */
    public function getTotalProteinsAttribute(): float
    {
        return $this->items->sum('proteins');
    }

    /**
     * Получить общее количество жиров из всех элементов.
     */
    public function getTotalFatsAttribute(): float
    {
        return $this->items->sum('fats');
    }

    /**
     * Получить общее количество углеводов из всех элементов.
     */
    public function getTotalCarbsAttribute(): float
    {
        return $this->items->sum('carbs');
    }

    /**
     * Проверить, есть ли элементы в приёме пищи.
     */
    public function hasItems(): bool
    {
        return $this->items()->exists();
    }

    /**
     * Получить количество элементов в приёме пищи.
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }
} 