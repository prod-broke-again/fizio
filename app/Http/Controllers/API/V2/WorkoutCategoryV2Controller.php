<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\WorkoutCategoryV2Resource;
use App\Models\WorkoutCategoryV2;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class WorkoutCategoryV2Controller extends Controller
{
    /**
     * Получить список всех активных категорий тренировок
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $gender = $request->query('gender');
        
        $categories = Cache::remember(
            "workout_categories_v2_{$gender}",
            3600,
            function () use ($gender) {
                $query = WorkoutCategoryV2::query()
                    ->where('is_active', true)
                    ->with(['workoutPrograms' => function ($query) {
                        $query->where('is_active', true)
                            ->orderBy('sort_order');
                    }])
                    ->orderBy('sort_order');
                
                if ($gender && in_array($gender, ['male', 'female'])) {
                    $query->where('gender', $gender);
                }
                
                return $query->get();
            }
        );
        
        return WorkoutCategoryV2Resource::collection($categories);
    }
    
    /**
     * Получить конкретную категорию по slug
     */
    public function show(string $slug): JsonResponse
    {
        $category = Cache::remember(
            "workout_category_v2_{$slug}",
            3600,
            function () use ($slug) {
                return WorkoutCategoryV2::where('slug', $slug)
                    ->where('is_active', true)
                    ->with(['workoutPrograms' => function ($query) {
                        $query->where('is_active', true)
                            ->orderBy('sort_order')
                            ->with(['workoutExercises' => function ($query) {
                                $query->orderBy('sort_order');
                            }]);
                    }])
                    ->firstOrFail();
            }
        );
        
        return response()->json([
            'success' => true,
            'data' => new WorkoutCategoryV2Resource($category)
        ]);
    }
}
