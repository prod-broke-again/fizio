<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserProgress;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    /**
     * Получение дневного прогресса пользователя
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dailyProgress()
    {
        $user = Auth::user();
        $progress = UserProgress::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();

        if (!$progress) {
            $progress = new UserProgress([
                'user_id' => $user->id,
                'calories' => 0,
                'steps' => 0,
                'workout_time' => 0,
                'water_intake' => 0,
                'daily_progress' => 0
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'calories' => $progress->calories,
                'steps' => $progress->steps,
                'workout_time' => $progress->workout_time,
                'water_intake' => $progress->water_intake,
                'daily_progress' => $progress->daily_progress
            ]
        ]);
    }

    /**
     * Обновление прогресса пользователя
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProgress(Request $request)
    {
        $request->validate([
            'calories' => 'nullable|integer|min:0',
            'steps' => 'nullable|integer|min:0',
            'workout_time' => 'nullable|integer|min:0',
            'water_intake' => 'nullable|integer|min:0',
            'daily_progress' => 'nullable|integer|min:0|max:100'
        ]);

        $user = Auth::user();
        $progress = UserProgress::firstOrNew([
            'user_id' => $user->id,
            'created_at' => today()
        ]);

        // Обновляем только предоставленные поля
        if ($request->has('calories')) {
            $progress->calories = $request->calories;
        }
        
        if ($request->has('steps')) {
            $progress->steps = $request->steps;
        }
        
        if ($request->has('workout_time')) {
            $progress->workout_time = $request->workout_time;
        }
        
        if ($request->has('water_intake')) {
            $progress->water_intake = $request->water_intake;
        }
        
        if ($request->has('daily_progress')) {
            $progress->daily_progress = $request->daily_progress;
        }

        $progress->save();

        return response()->json([
            'success' => true,
            'data' => [
                'calories' => $progress->calories,
                'steps' => $progress->steps,
                'workout_time' => $progress->workout_time,
                'water_intake' => $progress->water_intake,
                'daily_progress' => $progress->daily_progress
            ]
        ]);
    }
} 