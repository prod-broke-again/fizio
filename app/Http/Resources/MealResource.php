<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealResource extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type,
            'date' => $this->date->format('Y-m-d'),
            'time' => $this->time,
            'completed' => $this->completed,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Элементы приёма пищи
            'items' => MealItemResource::collection($this->whenLoaded('items')),
            
            // Агрегаты (если загружены)
            'totals' => [
                'calories' => $this->when($this->relationLoaded('items'), $this->total_calories, 0),
                'proteins' => $this->when($this->relationLoaded('items'), $this->total_proteins, 0),
                'fats' => $this->when($this->relationLoaded('items'), $this->total_fats, 0),
                'carbs' => $this->when($this->relationLoaded('items'), $this->total_carbs, 0),
                'items_count' => $this->when($this->relationLoaded('items'), $this->items_count, 0),
            ],
            
            // Связи
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
