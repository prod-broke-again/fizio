<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\WorkoutProgramV2Resource;
use App\Models\WorkoutProgramV2;
use App\Services\V2\WorkoutServiceV2;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class WorkoutProgramV2Controller extends Controller
{
    public function __construct(
        private readonly WorkoutServiceV2 $workoutService
    ) {}

    /**
     * Получить список всех активных программ тренировок
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $this->validateFilters($request);
        
        $cacheKey = $this->buildCacheKey($filters);
        
        $programs = Cache::remember($cacheKey, 3600, function () use ($filters) {
            return $this->workoutService->getFilteredPrograms($filters);
        });
        
        return WorkoutProgramV2Resource::collection($programs);
    }
    
    /**
     * Получить конкретную программу по slug
     */
    public function show(string $slug): JsonResponse
    {
        $program = Cache::remember(
            "workout_program_v2_{$slug}",
            3600,
            function () use ($slug) {
                return WorkoutProgramV2::where('slug', $slug)
                    ->where('is_active', true)
                    ->with(['category', 'workoutExercises' => function ($query) {
                        $query->orderBy('sort_order');
                    }])
                    ->firstOrFail();
            }
        );
        
        return response()->json([
            'success' => true,
            'data' => new WorkoutProgramV2Resource($program)
        ]);
    }
    
    /**
     * Получить программы по категории
     */
    public function getByCategory(string $categorySlug): AnonymousResourceCollection
    {
        $programs = Cache::remember(
            "workout_programs_by_category_{$categorySlug}",
            3600,
            function () use ($categorySlug) {
                return WorkoutProgramV2::whereHas('category', function ($query) use ($categorySlug) {
                    $query->where('slug', $categorySlug)->where('is_active', true);
                })
                ->where('is_active', true)
                ->with(['category', 'workoutExercises' => function ($query) {
                    $query->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->get();
            }
        );
        
        return WorkoutProgramV2Resource::collection($programs);
    }
    
    /**
     * Валидация фильтров запроса
     */
    private function validateFilters(Request $request): array
    {
        $validated = $request->validate([
            'category_id' => 'nullable|uuid|exists:workout_categories_v2,id',
            'difficulty_level' => 'nullable|in:beginner,intermediate,advanced',
            'is_free' => 'nullable|boolean',
            'gender' => 'nullable|in:male,female',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);
        
        return array_filter($validated);
    }
    
    /**
     * Построение ключа кеша на основе фильтров
     */
    private function buildCacheKey(array $filters): string
    {
        $filterString = empty($filters) ? 'all' : md5(serialize($filters));
        return "workout_programs_v2_{$filterString}";
    }
}
