<?php

declare(strict_types=1);

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserWorkoutProgressV2Resource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'program' => new WorkoutProgramV2Resource($this->whenLoaded('program')),
            'exercise' => new WorkoutExerciseV2Resource($this->whenLoaded('exercise')),
            'completed_at' => $this->completed_at?->toISOString(),
            'duration_seconds' => $this->duration_seconds,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
