<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TelegramAuthController extends Controller
{
    protected $telegramService;
    
    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }
    
    /**
     * Аутентификация пользователя через Telegram
     */
    public function auth(Request $request)
    {
        try {
            Log::debug('Получен запрос аутентификации через Telegram', ['headers' => $request->headers->all()]);
            
            // Получаем данные из запроса
            $telegramData = null;
            
            // Проверяем, пришли ли данные в параметре initData
            if ($request->has('initData')) {
                $initDataString = $request->input('initData');
                Log::debug('Получены данные в параметре initData', ['initData' => $initDataString]);
                
                // Парсим строку initData
                parse_str($initDataString, $telegramData);
            } 
            // Или данные пришли напрямую в теле запроса
            elseif ($request->has('hash') && $request->has('auth_date')) {
                $telegramData = $request->all();
                Log::debug('Получены данные напрямую в теле запроса', ['data' => $telegramData]);
            }
            
            // Проверяем наличие данных
            if (empty($telegramData)) {
                Log::error('Ошибка аутентификации Telegram: отсутствуют данные');
                return response()->json(['message' => 'Отсутствуют данные аутентификации Telegram'], 400);
            }
            
            // Валидируем данные от Telegram
            if (!$this->telegramService->validateWebAppData($telegramData)) {
                Log::error('Ошибка аутентификации Telegram: невалидные данные', ['data' => $telegramData]);
                return response()->json(['message' => 'Невалидные данные аутентификации Telegram'], 400);
            }
            
            // Получаем данные пользователя
            $userData = null;
            
            // Проверяем, есть ли данные пользователя в поле user
            if (isset($telegramData['user'])) {
                try {
                    $userData = json_decode($telegramData['user'], true);
                    Log::debug('Декодированы данные пользователя из поля user', ['userData' => $userData]);
                } catch (\Exception $e) {
                    Log::error('Ошибка декодирования данных пользователя', ['error' => $e->getMessage()]);
                }
            } 
            // Проверяем, есть ли данные пользователя напрямую в объекте
            elseif (isset($telegramData['id'])) {
                $userData = [
                    'id' => $telegramData['id'],
                    'first_name' => $telegramData['first_name'] ?? null,
                    'last_name' => $telegramData['last_name'] ?? null,
                    'username' => $telegramData['username'] ?? null,
                    'photo_url' => $telegramData['photo_url'] ?? null,
                ];
                Log::debug('Получены данные пользователя напрямую из объекта', ['userData' => $userData]);
            }
            
            // Проверяем наличие данных пользователя
            if (empty($userData) || !isset($userData['id'])) {
                Log::error('Ошибка аутентификации Telegram: отсутствуют данные пользователя');
                return response()->json(['message' => 'Отсутствуют данные пользователя Telegram'], 400);
            }
            
            // Ищем пользователя по telegram_id или создаем нового
            $user = User::firstOrCreate(
                ['telegram_id' => $userData['id']],
                [
                    'name' => $userData['first_name'] . ' ' . ($userData['last_name'] ?? ''),
                    'email' => 'telegram_' . $userData['id'] . '@example.com',
                    'password' => bcrypt(bin2hex(random_bytes(16))),
                    'telegram_username' => $userData['username'] ?? null,
                    'gender' => 'not-specified',
                ]
            );
            
            // Если пользователь найден, обновляем его данные
            if ($user->wasRecentlyCreated === false) {
                $user->update([
                    'telegram_username' => $userData['username'] ?? $user->telegram_username,
                    'name' => $userData['first_name'] . ' ' . ($userData['last_name'] ?? ''),
                ]);
            }
            
            // Аутентифицируем пользователя
            Auth::login($user);
            
            // Генерируем токен
            $token = $user->createToken('telegram-auth')->plainTextToken;
            
            Log::info('Успешная аутентификация пользователя через Telegram', ['user_id' => $user->id, 'telegram_id' => $user->telegram_id]);
            
            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Непредвиденная ошибка аутентификации Telegram', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Ошибка аутентификации: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Привязка аккаунта Telegram к существующему пользователю
     */
    public function link(Request $request)
    {
        try {
            // Проверяем, что пользователь аутентифицирован
            if (!Auth::check()) {
                return response()->json(['message' => 'Требуется аутентификация'], 401);
            }
            
            // Получаем данные из запроса
            $initDataString = $request->input('initData');
            
            if (empty($initDataString)) {
                return response()->json(['message' => 'Отсутствует параметр initData'], 400);
            }
            
            // Парсим данные
            parse_str($initDataString, $telegramData);
            
            // Валидируем данные от Telegram
            if (!$this->telegramService->validateWebAppData($telegramData)) {
                return response()->json(['message' => 'Невалидные данные Telegram'], 400);
            }
            
            // Получаем данные пользователя
            $userData = json_decode($telegramData['user'] ?? '{}', true);
            
            if (empty($userData) || !isset($userData['id'])) {
                return response()->json(['message' => 'Отсутствуют данные пользователя Telegram'], 400);
            }
            
            // Проверяем, не привязан ли уже этот Telegram аккаунт к другому пользователю
            $existingUser = User::where('telegram_id', $userData['id'])->first();
            
            if ($existingUser && $existingUser->id !== Auth::id()) {
                return response()->json(['message' => 'Этот аккаунт Telegram уже привязан к другому пользователю'], 400);
            }
            
            // Обновляем текущего пользователя
            $user = Auth::user();
            $user->update([
                'telegram_id' => $userData['id'],
                'telegram_username' => $userData['username'] ?? null,
            ]);
            
            return response()->json([
                'message' => 'Аккаунт Telegram успешно привязан',
                'user' => $user,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Ошибка привязки аккаунта Telegram', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Ошибка привязки аккаунта: ' . $e->getMessage()], 500);
        }
    }
} 