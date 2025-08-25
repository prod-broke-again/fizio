<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealItem extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые можно массово присваивать.
     */
    protected $fillable = [
        'meal_id',
        'product_id',
        'free_text',
        'grams',
        'servings',
        'calories',
        'proteins',
        'fats',
        'carbs',
    ];

    /**
     * Атрибуты, которые должны быть приведены к типам.
     */
    protected $casts = [
        'grams' => 'decimal:2',
        'servings' => 'decimal:2',
        'calories' => 'decimal:2',
        'proteins' => 'decimal:2',
        'fats' => 'decimal:2',
        'carbs' => 'decimal:2',
    ];

    /**
     * Отношение к приёму пищи.
     */
    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    /**
     * Отношение к продукту.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Получить название продукта (из базы или свободный текст).
     */
    public function getProductNameAttribute(): string
    {
        if ($this->product_id && $this->product) {
            return $this->product->name;
        }
        
        return $this->free_text ?? 'Неизвестный продукт';
    }

    /**
     * Получить вес в граммах (если указан).
     */
    public function getWeightAttribute(): ?float
    {
        return $this->grams;
    }

    /**
     * Получить количество порций (если указано).
     */
    public function getPortionsAttribute(): ?float
    {
        return $this->servings;
    }

    /**
     * Проверить, является ли продукт свободным текстом.
     */
    public function isFreeText(): bool
    {
        return !empty($this->free_text) && empty($this->product_id);
    }

    /**
     * Проверить, является ли продукт из базы данных.
     */
    public function isFromDatabase(): bool
    {
        return !empty($this->product_id) && $this->product;
    }
}
