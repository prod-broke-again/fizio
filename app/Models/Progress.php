<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Progress extends Model
{
    use HasUuids;

    protected $fillable = [
        'id', // UUID
        'user_id',
        'date', // Дата записи прогресса
        // Дневной прогресс (может быть null, если это запись другого типа, например, только измерения)
        'calories',
        'steps',
        'workout_time', // в минутах
        'water_intake', // в литрах
        // Измерения (могут быть null, если это запись другого типа)
        'weight', // кг
        'body_fat_percentage', // %
        'measurements', // JSON: { chest, waist, hips, arms, thighs, calves, shoulders }
        'photos', // JSON: массив URL фотографий
    ];

    protected $casts = [
        'id' => 'string',
        'date' => 'date:Y-m-d',
        'calories' => 'integer',
        'steps' => 'integer',
        'workout_time' => 'integer',
        'water_intake' => 'float',
        'weight' => 'float',
        'body_fat_percentage' => 'float',
        'measurements' => 'array',
        'photos' => 'array',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Метод calculateDailyProgress может быть пересмотрен или удален,
    // так как "дневной прогресс" может быть одним из видов записей в этой таблице,
    // а не вычисляемым полем из всех подряд.
    // Или его логика должна учитывать, что не все поля могут быть заполнены.
    /*
    public function calculateDailyProgress(): float
    {
        $caloriesGoal = $this->user->fitnessGoal->target_calories ?? 2000; // Пример получения цели
        $stepsGoal = $this->user->fitnessGoal->target_steps ?? 10000;
        // ... и т.д. для других целей ...

        $progressValue = 0;
        $metricsCount = 0;

        if (!is_null($this->calories) && $caloriesGoal > 0) {
            $progressValue += ($this->calories / $caloriesGoal * 0.3); // Веса можно настроить
            $metricsCount++;
        }
        // ... и т.д. для steps, workout_time, water_intake ...
        
        return $metricsCount > 0 ? ($progressValue / $metricsCount) * 100 : 0; // Усредненный прогресс
    }
    */
} 