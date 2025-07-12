<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Progress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthKitController extends Controller
{
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'steps' => 'required|integer|min:0',
            'calories' => 'required|integer|min:0',
            'distance' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:0',
            'timestamp' => 'required|date',
        ]);

        $progress = Progress::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'date' => now()->toDateString(),
            ],
            [
                'calories' => $validated['calories'],
                'steps' => $validated['steps'],
                'workout_time' => $validated['duration'],
                'water_intake' => 0, // По умолчанию, так как HealthKit не предоставляет эти данные
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'daily_progress' => $progress->calculateDailyProgress(),
                'calories' => $progress->calories,
                'steps' => $progress->steps,
                'workout_time' => $progress->workout_time,
                'water_intake' => $progress->water_intake,
            ]
        ]);
    }
} 