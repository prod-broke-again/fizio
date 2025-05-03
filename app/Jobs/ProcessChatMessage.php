<?php

namespace App\Jobs;

use App\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ProcessChatMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chatMessage;
    
    // Установим максимальное количество попыток
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(ChatMessage $chatMessage)
    {
        $this->chatMessage = $chatMessage;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Логируем начало обработки
            Log::info('ProcessChatMessage: начало обработки', [
                'message_id' => $this->chatMessage->id,
                'user_id' => $this->chatMessage->user_id
            ]);
            
            // Получение ответа от AI
            $response = $this->getAiResponse($this->chatMessage->message);

            // Обновление записи в БД
            $this->chatMessage->update([
                'response' => $response,
                'is_processing' => false
            ]);

            // Отправка сообщения в Redis для WebSocket сервера
            $messageData = json_encode([
                'id' => $this->chatMessage->id,
                'user_id' => $this->chatMessage->user_id,
                'message' => $this->chatMessage->message,
                'response' => $response,
                'created_at' => $this->chatMessage->created_at,
                'is_processing' => false
            ]);
            
            try {
                Redis::publish('chat:messages', $messageData);
                
                Log::info('Ответ чата опубликован в Redis (фоновая обработка)', [
                    'message_id' => $this->chatMessage->id,
                    'user_id' => $this->chatMessage->user_id,
                    'channel' => 'chat:messages',
                    'response_length' => strlen($response)
                ]);
            } catch (\Exception $e) {
                Log::error('Ошибка публикации в Redis: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('AI Processing Error: ' . $e->getMessage());

            $this->chatMessage->update([
                'response' => 'Произошла ошибка при обработке вашего запроса.',
                'is_processing' => false
            ]);
            
            // Публикуем сообщение об ошибке в Redis
            try {
                Redis::publish('chat:messages', json_encode([
                    'id' => $this->chatMessage->id,
                    'user_id' => $this->chatMessage->user_id,
                    'message' => $this->chatMessage->message,
                    'response' => 'Произошла ошибка при обработке вашего запроса.',
                    'created_at' => $this->chatMessage->created_at,
                    'is_processing' => false,
                    'error' => true
                ]));
            } catch (\Exception $redisEx) {
                Log::error('Ошибка публикации сообщения об ошибке в Redis: ' . $redisEx->getMessage());
            }
            
            // Пробросим исключение чтобы задача попала в failed jobs
            throw $e;
        }
    }

    /**
     * Получить ответ от AI-модели
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
        // Сначала проверим доступные модели
        $this->checkAvailableModels();
        
        // Получаем историю сообщений пользователя (последние 5)
        $chatHistory = $this->getChatHistory();
        
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
        
        // Добавляем текущее сообщение, если оно еще не в истории
        if (empty($chatHistory) || $chatHistory[count($chatHistory) - 1]->message !== $message) {
            $messages[] = [
                'role' => 'user',
                'content' => $message
            ];
        }
        
        $requestData = [
            'model' => config('services.gptunnel.model'),
            'messages' => $messages,
            'max_tokens' => 500,
            'temperature' => 0.7,
            'useWalletBalance' => true
        ];
        
        // Логируем детали запроса для диагностики
        Log::info('Запрос к GPTunnel', [
            'url' => 'https://gptunnel.ru/v1/chat/completions',
            'model' => config('services.gptunnel.model'),
            'api_key_length' => strlen(config('services.gptunnel.api_key')),
            'api_key_format' => substr(config('services.gptunnel.api_key'), 0, 10) . '...',
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
                'status' => $response->status(),
                'request_data' => $requestData
            ]);
            throw new \Exception('Ошибка при запросе к GPTunnel API');
        }
    }

    /**
     * Получить историю сообщений пользователя для сохранения контекста
     */
    private function getChatHistory()
    {
        // Получаем до 5 последних сообщений пользователя
        return \App\Models\ChatMessage::where('user_id', $this->chatMessage->user_id)
            ->where('id', '!=', $this->chatMessage->id) // Исключаем текущее сообщение
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->reverse(); // Переворачиваем коллекцию, чтобы сообщения шли в хронологическом порядке
    }

    private function checkAvailableModels()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.gptunnel.api_key'),
                'Content-Type' => 'application/json',
            ])->get('https://gptunnel.ru/v1/models');
            
            if ($response->successful()) {
                $models = $response->json();
                Log::info('Доступные модели GPTunnel:', [
                    'models' => $models,
                    'status' => 'success'
                ]);
            } else {
                Log::error('Ошибка при получении списка моделей:', [
                    'status_code' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Исключение при получении списка моделей:', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
