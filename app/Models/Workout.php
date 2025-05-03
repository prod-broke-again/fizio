<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые можно массово назначать
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'date',
        'duration',
        'calories',
        'image_url'
    ];

    /**
     * Атрибуты, которые должны быть приведены к нативным типам
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'datetime',
        'duration' => 'integer',
        'calories' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Получить пользователя, которому принадлежит тренировка
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 