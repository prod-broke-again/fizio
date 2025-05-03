<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $apiUrl = 'https://api.telegram.org/bot';
    protected ?string $token;
    
    public function __construct()
    {
        $this->token = config('telegram.bots.' . config('telegram.default') . '.token');
    }
    
    /**
     * Отправка сообщения в Telegram
     */
    public function sendMessage(string $chatId, string $text, array $options = []): array
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
        ], $options);
        
        return $this->callApi('sendMessage', $params);
    }
    
    /**
     * Установка webhook для Telegram бота
     */
    public function setWebhook(string $url, array $options = []): array
    {
        $params = array_merge([
            'url' => $url,
        ], $options);
        
        return $this->callApi('setWebhook', $params);
    }
    
    /**
     * Получение информации о webhook
     */
    public function getWebhookInfo(): array
    {
        return $this->callApi('getWebhookInfo');
    }
    
    /**
     * Удаление webhook
     */
    public function deleteWebhook(bool $dropPendingUpdates = false): array
    {
        return $this->callApi('deleteWebhook', [
            'drop_pending_updates' => $dropPendingUpdates,
        ]);
    }
    
    /**
     * Отправка запроса к Telegram API
     */
    protected function callApi(string $method, array $params = []): array
    {
        if (empty($this->token)) {
            Log::error('Telegram API error: Token not configured');
            return [
                'ok' => false,
                'error_code' => 401,
                'description' => 'Токен Telegram бота не настроен. Проверьте переменную TELEGRAM_BOT_TOKEN в файле .env',
            ];
        }
        
        $url = $this->apiUrl . $this->token . '/' . $method;
        
        try {
            $response = Http::post($url, $params);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('Telegram API error', [
                'method' => $method,
                'response' => $response->json(),
            ]);
            
            return [
                'ok' => false,
                'error_code' => $response->status(),
                'description' => 'Error calling Telegram API',
            ];
        } catch (\Exception $e) {
            Log::error('Telegram API exception', [
                'method' => $method,
                'exception' => $e->getMessage(),
            ]);
            
            return [
                'ok' => false,
                'error_code' => 500,
                'description' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Валидация данных от Telegram WebApp
     * 
     * Реализация согласно документации: https://core.telegram.org/bots/webapps#validating-data-received-via-the-web-app
     */
    public function validateWebAppData(array $initData): bool
    {
        Log::debug('Проверка данных WebApp', ['data_keys' => array_keys($initData)]);
        
        // Проверка, что initData не пустой и токен бота настроен
        if (empty($initData) || empty($this->token)) {
            Log::error('Ошибка валидации Telegram: пустые данные или отсутствует токен', ['initData' => array_keys($initData)]);
            return false;
        }
        
        // Проверка наличия обязательных полей
        if (!isset($initData['hash'])) {
            Log::error('Ошибка валидации Telegram: отсутствует поле hash');
            return false;
        }
        
        // Проверка наличия auth_date
        if (!isset($initData['auth_date'])) {
            Log::error('Ошибка валидации Telegram: отсутствует поле auth_date');
            return false;
        }
        
        // В разработке можно пропустить проверку, если ваш бот в тестовом режиме
        if (app()->environment('local', 'development') && config('app.debug')) {
            Log::warning('Проверка данных Telegram пропущена в режиме разработки');
            return true;
        }
        
        // 1. Получение хеша из данных
        $hash = $initData['hash'];
        
        // 2. Создаем отдельный массив без поля 'hash'
        $dataToCheck = $initData;
        unset($dataToCheck['hash']);
        
        // 3. Сортируем ключи в алфавитном порядке
        ksort($dataToCheck);
        
        // 4. Создаем строку для проверки в формате "key=value\nkey=value"
        $dataCheckString = [];
        foreach ($dataToCheck as $key => $value) {
            // Обрабатываем значение в зависимости от типа
            if (is_array($value)) {
                // Если значение - массив или объект, пропускаем
                continue;
            } elseif (is_bool($value)) {
                // Преобразуем булево в строку
                $value = $value ? 'true' : 'false';
            }
            
            $dataCheckString[] = $key . '=' . $value;
        }
        
        // Объединяем параметры в одну строку через \n (важно: именно перенос строки, а не &)
        $dataCheckString = implode("\n", $dataCheckString);
        
        // 5. Создаем секретный ключ с использованием строки "WebAppData" и токена бота
        $secretKey = hash_hmac('sha256', 'WebAppData', $this->token, true);
        
        // 6. Создаем HMAC-SHA256 от dataCheckString с использованием secretKey
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);
        
        // Логируем детали для отладки
        Log::debug('Детали валидации данных Telegram', [
            'token_first_chars' => substr($this->token, 0, 5) . '...',
            'data_string' => $dataCheckString,
            'calculated_hash' => $calculatedHash,
            'received_hash' => $hash
        ]);
        
        // 7. Сравниваем вычисленный хеш с полученным
        $result = hash_equals($calculatedHash, $hash);
        
        if (!$result) {
            Log::error('Ошибка валидации Telegram: хеши не совпадают');
        }
        
        return $result;
    }
} 