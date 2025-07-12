<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Workout extends Model
{
    use HasFactory, HasUuids;

    /**
     * Атрибуты, которые можно массово назначать
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'name',
        'type',
        'exercises',
        'duration',
        'difficulty',
        'date',
        'completed',
        'calories_burned',
        'category',
        'image_url',
    ];

    /**
     * Атрибуты, которые должны быть приведены к нативным типам
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'exercises' => 'array',
        'duration' => 'integer',
        'date' => 'date:Y-m-d',
        'completed' => 'boolean',
        'calories_burned' => 'integer',
        'image_url' => 'string',
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

    /**
     * Получить пользователя, который создал тренировку
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 