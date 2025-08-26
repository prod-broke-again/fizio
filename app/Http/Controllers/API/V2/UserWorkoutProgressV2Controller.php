<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\StoreUserWorkoutProgressV2Request;
use App\Http\Requests\V2\UpdateUserWorkoutProgressV2Request;
use App\Http\Resources\V2\UserWorkoutProgressV2Resource;
use App\Models\UserWorkoutProgressV2;
use App\Services\V2\WorkoutProgressServiceV2;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class UserWorkoutProgressV2Controller extends Controller
{
    public function __construct(
        private readonly WorkoutProgressServiceV2 $progressService
    ) {}

    /**
     * Получить прогресс пользователя
     */
    public function index(): AnonymousResourceCollection
    {
        $progress = $this->progressService->getUserProgress(Auth::id());
        
        return UserWorkoutProgressV2Resource::collection($progress);
    }
    
    /**
     * Сохранить прогресс тренировки
     */
    public function store(StoreUserWorkoutProgressV2Request $request): JsonResponse
    {
        $progress = $this->progressService->storeProgress(
            Auth::id(),
            $request->validated()
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Прогресс успешно сохранен',
            'data' => new UserWorkoutProgressV2Resource($progress)
        ], 201);
    }
    
    /**
     * Обновить прогресс тренировки
     */
    public function update(
        UpdateUserWorkoutProgressV2Request $request,
        string $id
    ): JsonResponse {
        $progress = UserWorkoutProgressV2::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
            
        $updatedProgress = $this->progressService->updateProgress(
            $progress,
            $request->validated()
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Прогресс успешно обновлен',
            'data' => new UserWorkoutProgressV2Resource($updatedProgress)
        ]);
    }
    
    /**
     * Получить статистику прогресса
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->progressService->getUserStatistics(Auth::id());
        
        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }
}
