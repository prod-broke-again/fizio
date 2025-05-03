<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkoutController extends Controller
{
    /**
     * Получение расписания тренировок
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSchedule()
    {
        $user = Auth::user();
        $workouts = Workout::where('user_id', $user->id)
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $workouts
        ]);
    }

    /**
     * Добавление новой тренировки
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'duration' => 'required|integer|min:1',
            'calories' => 'required|integer|min:0',
            'image_url' => 'nullable|string|url'
        ]);

        $user = Auth::user();
        
        $workout = new Workout([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'duration' => $request->duration,
            'calories' => $request->calories,
            'image_url' => $request->image_url
        ]);

        $workout->save();

        return response()->json([
            'success' => true,
            'data' => $workout
        ]);
    }

    /**
     * Обновление тренировки
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
            'duration' => 'nullable|integer|min:1',
            'calories' => 'nullable|integer|min:0',
            'image_url' => 'nullable|string|url'
        ]);

        $user = Auth::user();
        $workout = Workout::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$workout) {
            return response()->json([
                'success' => false,
                'message' => 'Тренировка не найдена'
            ], 404);
        }

        // Обновляем только предоставленные поля
        if ($request->has('title')) {
            $workout->title = $request->title;
        }
        
        if ($request->has('description')) {
            $workout->description = $request->description;
        }
        
        if ($request->has('date')) {
            $workout->date = $request->date;
        }
        
        if ($request->has('duration')) {
            $workout->duration = $request->duration;
        }
        
        if ($request->has('calories')) {
            $workout->calories = $request->calories;
        }
        
        if ($request->has('image_url')) {
            $workout->image_url = $request->image_url;
        }

        $workout->save();

        return response()->json([
            'success' => true,
            'data' => $workout
        ]);
    }

    /**
     * Удаление тренировки
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $workout = Workout::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$workout) {
            return response()->json([
                'success' => false,
                'message' => 'Тренировка не найдена'
            ], 404);
        }

        $workout->delete();

        return response()->json([
            'success' => true
        ]);
    }
} 