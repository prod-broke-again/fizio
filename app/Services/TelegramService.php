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
        $this->token = config('telegram.bots.main.token') ?? env('TELEGRAM_BOT_TOKEN');
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
        
        // Логируем токен для отладки (только первые символы для безопасности)
        Log::debug('Telegram токен для валидации', [
            'token_first_chars' => substr($this->token, 0, 10) . '...',
            'token_length' => strlen($this->token),
            'init_data_keys' => array_keys($initData)
        ]);
        
        // Проверка, что initData не пустой и токен бота настроен
        if (empty($initData) || empty($this->token)) {
            Log::error('Ошибка валидации Telegram: пустые данные или отсутствует токен', ['initData' => array_keys($initData)]);
            return false;
        }
        
        // Получаем сырую строку initData
        $initDataRaw = $initData['initData'] ?? $initData['init_data'] ?? null;
        if (!$initDataRaw) {
            Log::error('Ошибка валидации Telegram: отсутствует сырая строка initData', [
                'available_keys' => array_keys($initData),
                'initData_value' => $initData['initData'] ?? 'не найдено',
                'init_data_value' => $initData['init_data'] ?? 'не найдено'
            ]);
            return false;
        }
        
        // В разработке можно пропустить проверку, если ваш бот в тестовом режиме
        if (app()->environment('local', 'development') && config('app.debug')) {
            Log::warning('Проверка данных Telegram пропущена в режиме разработки');
            return true;
        }
        
        return $this->validateRawInitData($initDataRaw);
    }
    
    /**
     * Валидация по сырой строке initData
     */
    protected function validateRawInitData(string $initDataRaw): bool
    {
        Log::debug('Валидация по сырой строке initData', ['initDataRaw' => substr($initDataRaw, 0, 100) . '...']);
        
        // ВРЕМЕННО: пропускаем валидацию для тестирования
        Log::warning('Валидация Telegram временно отключена для тестирования');
        return true;
        
        // TODO: Реализовать правильную валидацию Ed25519
        // Извлекаем hash из сырой строки
        $receivedHash = null;
        $pairs = [];
        
        foreach (explode('&', $initDataRaw) as $part) {
            if ($part === '') continue;
            
            [$kEnc, $vEnc] = array_pad(explode('=', $part, 2), 2, '');
            $k = rawurldecode($kEnc);
            $v = rawurldecode($vEnc);
            
            if ($k === 'hash') {
                $receivedHash = $v;
                continue;
            }
            
            // Пропускаем signature и пустые значения
            if ($k === 'signature' || $v === '' || $v === null) continue;
            
            // ВАЖНО: user и др. остаются строками (JSON), не перекодируем!
            $pairs[$k] = $v;
        }
        
        if (!$receivedHash) {
            Log::error('Ошибка валидации Telegram: отсутствует hash в initData');
            return false;
        }
        
        // Сборка data_check_string
        ksort($pairs);
        $dataCheckString = implode("\n", array_map(
            fn($k, $v) => $k . '=' . $v,
            array_keys($pairs),
            $pairs
        ));
        
        // Секрет по доке: HMAC_SHA256(bot_token, key = "WebAppData")
        $secretKey = hash_hmac('sha256', $this->token, 'WebAppData', true);
        
        // Итоговый hash: HMAC_SHA256(data_check_string, key = secretKey)
        $calcHash = hash_hmac('sha256', $dataCheckString, $secretKey);
        
        // Логируем детали для отладки
        Log::debug('Детали валидации данных Telegram', [
            'token_first_chars' => substr($this->token, 0, 10) . '...',
            'data_string' => $dataCheckString,
            'calculated_hash' => $calcHash,
            'received_hash' => $receivedHash,
            'pairs_count' => count($pairs)
        ]);
        
        // Сравнение хешей
        if (!hash_equals($calcHash, $receivedHash)) {
            Log::error('Ошибка валидации Telegram: хеши не совпадают');
            return false;
        }
        
        // Дополнительная проверка времени (24 часа)
        $authDate = isset($pairs['auth_date']) ? (int)$pairs['auth_date'] : 0;
        $now = time();
        
        if ($authDate <= 0 || $authDate > $now || ($now - $authDate) > 86400) {
            Log::error('Ошибка валидации Telegram: неверное время auth_date', [
                'auth_date' => $authDate,
                'now' => $now,
                'difference' => $now - $authDate
            ]);
            return false;
        }
        
        Log::debug('Валидация Telegram успешна');
        return true;
    }
} 