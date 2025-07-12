<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeekPlan extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'meals',
        'workouts',
        'progress'
    ];

    protected $casts = [
        'meals' => 'array',
        'workouts' => 'array',
        'progress' => 'integer',
        'date' => 'date:Y-m-d',
    ];

    protected $attributes = [
        'meals' => '[]',
        'workouts' => '[]',
        'progress' => 0
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 