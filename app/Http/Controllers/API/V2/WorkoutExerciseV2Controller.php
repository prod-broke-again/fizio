<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\WorkoutExerciseV2Resource;
use App\Models\WorkoutExerciseV2;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class WorkoutExerciseV2Controller extends Controller
{
    /**
     * Получить конкретное упражнение по ID
     */
    public function show(string $id): JsonResponse
    {
        $exercise = Cache::remember(
            "workout_exercise_v2_{$id}",
            3600,
            function () use ($id) {
                return WorkoutExerciseV2::with(['program.category'])
                    ->findOrFail($id);
            }
        );
        
        return response()->json([
            'success' => true,
            'data' => new WorkoutExerciseV2Resource($exercise)
        ]);
    }
    
    /**
     * Получить упражнения по программе
     */
    public function getByProgram(string $programId): AnonymousResourceCollection
    {
        $exercises = Cache::remember(
            "workout_exercises_by_program_{$programId}",
            3600,
            function () use ($programId) {
                return WorkoutExerciseV2::where('program_id', $programId)
                    ->with(['program.category'])
                    ->orderBy('sort_order')
                    ->get();
            }
        );
        
        return WorkoutExerciseV2Resource::collection($exercises);
    }
}
