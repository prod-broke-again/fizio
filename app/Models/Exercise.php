<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exercise extends Model
{
    protected $fillable = [
        'workout_id',
        'name',
        'description',
        'duration',
        'sets',
        'repetitions',
        'rest_time',
        'image_url',
        'video_url',
    ];

    protected $casts = [
        'duration' => 'integer',
        'sets' => 'integer',
        'repetitions' => 'integer',
        'rest_time' => 'integer',
    ];

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }
} 