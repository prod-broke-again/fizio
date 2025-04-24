<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user()
            ],
            'message' => 'Профиль пользователя'
        ]);
    }

    public function saveFitnessGoal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'goal' => 'required|string|in:weight-loss,muscle-gain,maintenance',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $user->fitness_goal = $request->goal;
        $user->save();

        return response()->json([
            'success' => true,
            'data' => [
                'goal' => $user->fitness_goal,
                'updated_at' => $user->updated_at
            ],
            'message' => 'Цель фитнеса сохранена'
        ]);
    }

    public function getFitnessGoal(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'goal' => $user->fitness_goal,
                'updated_at' => $user->updated_at
            ],
            'message' => 'Текущая цель фитнеса'
        ]);
    }
} 