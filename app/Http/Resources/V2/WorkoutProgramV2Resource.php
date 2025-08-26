<?php

declare(strict_types=1);

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkoutProgramV2Resource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'difficulty_level' => $this->difficulty_level,
            'duration_weeks' => $this->duration_weeks,
            'calories_per_workout' => $this->calories_per_workout,
            'is_free' => $this->is_free,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'category' => new WorkoutCategoryV2Resource($this->whenLoaded('category')),
            'workout_exercises' => WorkoutExerciseV2Resource::collection($this->whenLoaded('workoutExercises')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
