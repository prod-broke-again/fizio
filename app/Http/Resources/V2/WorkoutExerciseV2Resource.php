<?php

declare(strict_types=1);

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkoutExerciseV2Resource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'video_url' => $this->video_url,
            'thumbnail_url' => $this->thumbnail_url,
            'duration_seconds' => $this->duration_seconds,
            'sets' => $this->sets,
            'reps' => $this->reps,
            'rest_seconds' => $this->rest_seconds,
            'equipment_needed' => $this->equipment_needed,
            'muscle_groups' => $this->muscle_groups,
            'sort_order' => $this->sort_order,
            'program' => new WorkoutProgramV2Resource($this->whenLoaded('program')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
