<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TelegramAuthController extends Controller
{
    protected TelegramService $telegramService;
    
    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }
    
    /**
     * Авторизация пользователя через Telegram
     */
    public function auth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'first_name' => 'required|string',
            'auth_date' => 'required|numeric',
            'hash' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Проверка актуальности данных (не старше 24 часов)
        if (time() - $request->auth_date > 86400) {
            return response()->json([
                'success' => false,
                'message' => 'Данные авторизации устарели',
            ], 401);
        }
        
        // Проверка подписи данных
        if (!$this->telegramService->validateWebAppData($request->all())) {
            return response()->json([
                'success' => false,
                'message' => 'Недействительная подпись данных',
            ], 401);
        }
        
        // Проверяем, существует ли пользователь с таким telegram_id
        $user = User::where('telegram_id', $request->id)->first();
        
        if (!$user) {
            // Создаем нового пользователя
            $user = User::create([
                'name' => $request->first_name . ' ' . ($request->last_name ?? ''),
                'email' => $request->id . '@telegram.user',
                'password' => Hash::make(uniqid()),
                'telegram_id' => $request->id,
                'telegram_username' => $request->username ?? null,
            ]);
        } else {
            // Обновляем данные пользователя
            $user->update([
                'name' => $request->first_name . ' ' . ($request->last_name ?? ''),
                'telegram_username' => $request->username ?? $user->telegram_username,
            ]);
        }
        
        // Генерируем токен доступа
        $token = $user->createToken('telegram-auth')->plainTextToken;
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
            'message' => 'Авторизация через Telegram успешна',
        ]);
    }
    
    /**
     * Связывание существующего аккаунта с Telegram ID
     */
    public function link(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telegram_id' => 'required|numeric',
            'telegram_username' => 'nullable|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Находим пользователя по email
        $user = User::where('email', $request->email)->first();
        
        // Проверяем пароль
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный email или пароль',
            ], 401);
        }
        
        // Проверяем, не привязан ли уже этот Telegram ID к другому аккаунту
        $existingUser = User::where('telegram_id', $request->telegram_id)
            ->where('id', '!=', $user->id)
            ->first();
            
        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'Этот Telegram аккаунт уже привязан к другому пользователю',
            ], 409);
        }
        
        // Связываем аккаунт с Telegram ID
        $user->update([
            'telegram_id' => $request->telegram_id,
            'telegram_username' => $request->telegram_username,
        ]);
        
        // Генерируем новый токен
        $token = $user->createToken('telegram-link')->plainTextToken;
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
            'message' => 'Аккаунт успешно связан с Telegram',
        ]);
    }
} 