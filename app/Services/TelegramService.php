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
     */
    public function validateWebAppData(array $initData): bool
    {
        // Проверка, что initData не пустой и токен бота настроен
        if (empty($initData) || !isset($initData['hash']) || empty($this->token)) {
            return false;
        }
        
        // Получение хеша из данных
        $hash = $initData['hash'];
        unset($initData['hash']);
        
        // Сортировка параметров в алфавитном порядке
        ksort($initData);
        
        // Формирование строки для проверки
        $dataCheckString = '';
        foreach ($initData as $key => $value) {
            $dataCheckString .= $key . '=' . $value . "\n";
        }
        $dataCheckString = trim($dataCheckString);
        
        // Генерация секретного ключа на основе токена бота
        $secretKey = hash('sha256', $this->token, true);
        
        // Вычисление и проверка хеша
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);
        
        return $calculatedHash === $hash;
    }
} 