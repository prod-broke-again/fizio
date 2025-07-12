<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Food extends Model
{
    protected $fillable = [
        'meal_id',
        'name',
        'calories',
        'proteins',
        'carbs',
        'fats',
    ];

    protected $casts = [
        'calories' => 'integer',
        'proteins' => 'float',
        'carbs' => 'float',
        'fats' => 'float',
    ];

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }
} 