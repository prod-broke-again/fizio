<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();
        
        $userData = $user->toArray();
        
        // Добавляем URL аватара, если аватар существует
        if ($user->avatar) {
            $userData['avatar_url'] = url(Storage::url($user->avatar));
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $userData
            ],
            'message' => 'Профиль пользователя'
        ]);
    }
    
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $request->user()->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gender' => 'nullable|string|in:male,female',
            'current_password' => 'required_with:password|string',
            'password' => 'sometimes|required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Проверка текущего пароля при изменении
        if ($request->has('password') && !Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Текущий пароль указан неверно',
                'errors' => ['current_password' => ['Текущий пароль указан неверно']]
            ], 422);
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }
        
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        
        if ($request->has('gender')) {
            $user->gender = $request->gender;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }
        
        // Обработка загрузки аватара
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            // Удаление старого аватара, если он существует
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            // Загрузка нового аватара
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }
        
        $user->save();
        
        // Формируем полный URL для аватара, если он есть
        $userData = $user->toArray();
        if ($user->avatar) {
            $userData['avatar_url'] = url(Storage::url($user->avatar));
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $userData
            ],
            'message' => 'Профиль успешно обновлен'
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
    
    public function getGender(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'data' => [
                'gender' => $user->gender
            ],
            'message' => 'Пол пользователя'
        ]);
    }
} 