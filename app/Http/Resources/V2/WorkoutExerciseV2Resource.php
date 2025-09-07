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
            'instructions' => $this->instructions,
            
            // Видео контент
            'video' => [
                'url' => $this->video_url,
                'file' => $this->video_file ? asset('storage/' . $this->video_file) : null,
                'has_video' => $this->hasVideo() || !empty($this->video_file),
            ],
            
            // Превью изображения
            'thumbnail' => [
                'url' => $this->thumbnail_url,
                'file' => $this->thumbnail_file ? asset('storage/' . $this->thumbnail_file) : null,
                'has_thumbnail' => $this->hasThumbnail() || !empty($this->thumbnail_file),
            ],
            
            // Характеристики упражнения
            'duration_seconds' => $this->duration_seconds,
            'duration_minutes' => $this->getDurationMinutes(),
            'sets' => $this->sets,
            'reps' => $this->reps,
            'total_reps' => $this->getTotalReps(),
            'rest_seconds' => $this->rest_seconds,
            'weight_kg' => $this->weight_kg,
            
            // Время выполнения
            'total_time_seconds' => $this->getTotalTimeSeconds(),
            'total_time_minutes' => $this->getTotalTimeMinutes(),
            'total_time_hours' => $this->getTotalTimeHours(),
            
            // Дополнительная информация
            'equipment_needed' => $this->equipment_needed,
            'equipment_list' => $this->getEquipmentList(),
            'muscle_groups' => $this->muscle_groups,
            'muscle_groups_list' => $this->getMuscleGroupsList(),
            'sort_order' => $this->sort_order,
            
            // Связанные данные
            'program' => new WorkoutProgramV2Resource($this->whenLoaded('program')),
            'program_name' => $this->getProgramName(),
            'category_name' => $this->getCategoryName(),
            'gender' => $this->getGender(),
            
            // Метаданные
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
