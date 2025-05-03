<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые можно массово назначать
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'calories',
        'steps',
        'workout_time',
        'water_intake',
        'daily_progress'
    ];

    /**
     * Атрибуты, которые должны быть приведены к нативным типам
     *
     * @var array<string, string>
     */
    protected $casts = [
        'calories' => 'integer',
        'steps' => 'integer',
        'workout_time' => 'integer',
        'water_intake' => 'integer',
        'daily_progress' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Получить пользователя, которому принадлежит прогресс
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 