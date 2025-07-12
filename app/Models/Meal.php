<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'calories',
        'proteins',
        'fats',
        'carbs',
        'food_id',
        'date',
        'completed',
        'time',
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

    // Связь с Food, если она нужна. По спецификации продукты (food) теперь часть Meal.
    // Если Food - это отдельная сущность со своей таблицей, связь можно оставить.
    // По текущей спецификации, все данные о еде (калории, белки, жиры, углеводы) хранятся прямо в Meal.
    // public function foods(): HasMany
    // {
    //     return $this->hasMany(Food::class);
    // }

    // Методы calculateTotalCalories и calculateMacros больше не нужны,
    // так как КБЖУ хранятся прямо в модели Meal.
    // Если они понадобятся для расчета по нескольким Meal, их можно вынести в сервис.
} 