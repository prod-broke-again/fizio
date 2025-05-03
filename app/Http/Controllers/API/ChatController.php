<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessChatMessage;
use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ChatController extends Controller
{
    private $useQueue;

    /**
     * Создать новый экземпляр контроллера.
     */
    public function __construct($useQueue = true)
    {
        $this->useQueue = $useQueue;
    }

    /**
     * Отправить сообщение и получить ответ от ассистента
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = auth()->user();
        $message = $request->input('message');

        // Создаем запись о сообщении (в процессе обработки)
        $chatMessage = ChatMessage::create([
            'user_id' => $user->id,
            'message' => $message,
            'response' => '',
            'is_processing' => true
        ]);

        // Публикуем начальный статус в Redis для уведомления о начале обработки
        $messageData = json_encode([
            'id' => $chatMessage->id,
            'user_id' => $user->id,
            'message' => $message,
            'response' => '',
            'created_at' => $chatMessage->created_at,
            'is_processing' => true
        ]);
            
        try {
            Redis::publish('chat:messages', $messageData);
            
            Log::info('Опубликовано сообщение чата в Redis (начало обработки)', [
                'message_id' => $chatMessage->id,
                'user_id' => $user->id,
                'channel' => 'chat:messages'
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка публикации в Redis: ' . $e->getMessage());
        }

        // Если используем очереди
        if ($this->useQueue) {
            // Помещаем задачу в очередь
            ProcessChatMessage::dispatch($chatMessage);

            return response()->json([
                'success' => true,
                'message' => 'Ваше сообщение обрабатывается',
                'data' => [
                    'message_id' => $chatMessage->id,
                    'message' => $message,
                    'is_processing' => true
                ]
            ]);
        }

        // Синхронная обработка
        try {
            // Получаем ответ от AI API
            $response = $this->getAiResponse($message);

            // Обновляем запись в БД
            $chatMessage->update([
                'response' => $response,
                'is_processing' => false
            ]);

            // Публикуем ответ в Redis для WebSocket сервера при синхронной обработке
            try {
                Redis::publish('chat:messages', json_encode([
                    'id' => $chatMessage->id,
                    'user_id' => $user->id,
                    'message' => $message,
                    'response' => $response,
                    'created_at' => $chatMessage->created_at,
                    'is_processing' => false
                ]));

                Log::info('Опубликован ответ чата в Redis (синхронная обработка)', [
                    'message_id' => $chatMessage->id,
                    'user_id' => $user->id,
                    'channel' => 'chat:messages'
                ]);
            } catch (\Exception $e) {
                Log::error('Ошибка публикации ответа в Redis: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => $chatMessage->message,
                    'response' => $chatMessage->response,
                    'created_at' => $chatMessage->created_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('AI Assistant error: ' . $e->getMessage());

            $errorMessage = 'Извините, произошла ошибка при обработке вашего запроса. Пожалуйста, попробуйте еще раз.';
            
            $chatMessage->update([
                'response' => $errorMessage,
                'is_processing' => false
            ]);

            // Публикуем сообщение об ошибке в Redis
            try {
                Redis::publish('chat:messages', json_encode([
                    'id' => $chatMessage->id,
                    'user_id' => $user->id,
                    'message' => $message,
                    'response' => $errorMessage,
                    'created_at' => $chatMessage->created_at,
                    'is_processing' => false,
                    'error' => true
                ]));
            } catch (\Exception $redisEx) {
                Log::error('Ошибка публикации сообщения об ошибке в Redis: ' . $redisEx->getMessage());
            }

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обработке запроса',
                'data' => [
                    'message' => $chatMessage->message,
                    'response' => $chatMessage->response,
                    'created_at' => $chatMessage->created_at
                ]
            ], 500);
        }
    }

    /**
     * Получить историю чата пользователя
     */
    public function getChatHistory(): JsonResponse
    {
        $user = auth()->user();
        
        $chatHistory = ChatMessage::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'response' => $message->response,
                    'created_at' => $message->created_at
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $chatHistory
        ]);
    }

    /**
     * Получить ответ от AI-модели
     * @throws \Exception
     */
    private function getAiResponse($message)
    {
        // Используем GPTunnel API
        return $this->getGeminiResponse($message);
    }

    /**
     * Получить ответ от GPTunnel API
     */
    private function getGeminiResponse($message)
    {
        // Получаем историю сообщений пользователя (последние 5)
        $user = auth()->user();
        $chatHistory = ChatMessage::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->reverse(); // Переворачиваем коллекцию, чтобы сообщения шли в хронологическом порядке
        
        // Формируем сообщения для API с учетом истории
        $messages = [
            [
                'role' => 'system',
                'content' => 'Вы - фитнес-ассистент в образе милой и дружелюбной панды, которая является маскотом приложения. Вы помогаете пользователям с вопросами о тренировках, питании и здоровом образе жизни. Отвечайте кратко и по существу, используя русский язык. Иногда можете добавлять небольшие эмоции, уместные для панды (например, "🐼 Панда рекомендует..." или "Как ваша панда-тренер, советую..."). Не перебарщивайте с ролью, главное - давать полезную информацию.'
            ]
        ];
        
        // Добавляем историю сообщений
        foreach ($chatHistory as $chat) {
            // Сообщение пользователя
            $messages[] = [
                'role' => 'user',
                'content' => $chat->message
            ];
            
            // Ответ ассистента (если есть)
            if (!empty($chat->response) && !$chat->is_processing) {
                $messages[] = [
                    'role' => 'assistant',
                    'content' => $chat->response
                ];
            }
        }
        
        // Добавляем текущее сообщение
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];
        
        $requestData = [
            'model' => config('services.gptunnel.model'),
            'messages' => $messages,
            'max_tokens' => 500,
            'temperature' => 0.7,
            'useWalletBalance' => true
        ];
        
        // Логируем детали запроса
        Log::info('Запрос к GPTunnel из контроллера', [
            'model' => config('services.gptunnel.model'),
            'message_length' => strlen($message),
            'history_count' => count($chatHistory),
            'messages_count' => count($messages)
        ]);
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.gptunnel.api_key'),
            'Content-Type' => 'application/json',
        ])->post('https://gptunnel.ru/v1/chat/completions', $requestData);

        if ($response->successful()) {
            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? 'Не удалось получить ответ';
        }

        // Проверяем конкретные коды ошибок
        if ($response->status() === 402) {
            Log::error('GPTunnel API error: Недостаточно средств на балансе аккаунта', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Для использования сервиса необходимо пополнить баланс GPTunnel API');
        } elseif ($response->status() === 401) {
            Log::error('GPTunnel API error: Ошибка авторизации (неверный API ключ)', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Ошибка авторизации в GPTunnel API');
        } elseif ($response->status() === 404) {
            Log::error('GPTunnel API error: Модель не найдена', [
                'status' => $response->status(),
                'body' => $response->body(),
                'model' => config('services.gptunnel.model')
            ]);
            throw new \Exception('Выбранная модель не найдена в GPTunnel API');
        } else {
            Log::error('GPTunnel API error: ' . $response->body(), [
                'status' => $response->status()
            ]);
            throw new \Exception('Ошибка при запросе к GPTunnel API');
        }
    }

    /**
     * Получить ответ на основе голосового ввода
     */
    public function processVoice(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,ogg|max:5120', // макс. 5MB
        ]);

        $user = auth()->user();
        $audioFile = $request->file('audio');
        $path = $audioFile->store('voice_messages', 'public');

        try {
            // Здесь может быть интеграция с сервисом распознавания речи
            // Например, Google Cloud Speech-to-Text

            // Временное решение - возвращаем сообщение об успешной загрузке
            return response()->json([
                'success' => true,
                'message' => 'Голосовое сообщение успешно обработано',
                'data' => [
                    'audio_path' => $path
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Voice processing error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обработке голосового сообщения',
            ], 500);
        }
    }

    /**
     * Тестовый метод для проверки доступности API GPTunnel
     */
    public function testGptunnel()
    {
        try {
            // 1. Проверка доступных моделей
            $modelResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.gptunnel.api_key'),
                'Content-Type' => 'application/json',
            ])->get('https://gptunnel.ru/v1/models');
            
            // 2. Тестовый запрос к чату
            $chatResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.gptunnel.api_key'),
                'Content-Type' => 'application/json',
            ])->post('https://gptunnel.ru/v1/chat/completions', [
                'model' => config('services.gptunnel.model'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Привет, как дела?'
                    ]
                ],
                'useWalletBalance' => true
            ]);
            
            return response()->json([
                'success' => true,
                'models_response' => $modelResponse->json(),
                'models_status' => $modelResponse->status(),
                'chat_response' => $chatResponse->json(),
                'chat_status' => $chatResponse->status(),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
