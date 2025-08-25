<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'meal_id' => $this->meal_id,
            'product_id' => $this->product_id,
            'free_text' => $this->free_text,
            'grams' => $this->grams,
            'servings' => $this->servings,
            'calories' => $this->calories,
            'proteins' => $this->proteins,
            'fats' => $this->fats,
            'carbs' => $this->carbs,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Вычисляемые атрибуты
            'product_name' => $this->product_name,
            'weight' => $this->weight,
            'portions' => $this->portions,
            'is_free_text' => $this->is_free_text(),
            'is_from_database' => $this->is_from_database(),
            
            // Связи
            'product' => new ProductResource($this->whenLoaded('product')),
            'meal' => new MealResource($this->whenLoaded('meal')),
        ];
    }
}
