<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutProgress extends Model
{
    protected $fillable = [
        'user_id',
        'workout_id',
        'duration',
        'calories_burned',
        'completed_at',
    ];

    protected $casts = [
        'duration' => 'integer',
        'calories_burned' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }
} 