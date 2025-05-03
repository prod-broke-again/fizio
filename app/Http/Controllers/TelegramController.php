<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    protected TelegramService $telegramService;
    
    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }
    
    /**
     * Обработка входящих Webhook запросов от Telegram
     */
    public function handleWebhook(Request $request)
    {
        $update = $request->all();
        
        Log::info('Telegram webhook received', ['update' => $update]);
        
        try {
            // Обработка разных типов обновлений
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
            }
            
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Error handling Telegram webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json(['status' => 'error'], 500);
        }
    }
    
    /**
     * Настройка Webhook для бота
     */
    public function setupWebhook()
    {
        $webhookUrl = config('telegram.bots.' . config('telegram.default') . '.webhook_url');
        
        if (empty($webhookUrl)) {
            $webhookUrl = url('/api/telegram/webhook');
        }
        
        $result = $this->telegramService->setWebhook($webhookUrl);
        
        return response()->json($result);
    }
    
    /**
     * Получение информации о Webhook
     */
    public function getWebhookInfo()
    {
        $result = $this->telegramService->getWebhookInfo();
        
        return response()->json($result);
    }
    
    /**
     * Обработка входящих сообщений
     */
    protected function handleMessage(array $message)
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        
        // Обработка команд
        if (isset($message['entities']) && $message['entities'][0]['type'] === 'bot_command') {
            $this->handleCommand($chatId, $text);
            return;
        }
        
        // Обработка обычных сообщений
        if ($text === 'Открыть приложение') {
            $this->sendOpenAppMessage($chatId);
        } else {
            // Любое другое сообщение будет перенаправлять на открытие приложения
            $this->sendWelcomeMessage($chatId);
        }
    }
    
    /**
     * Обработка команд бота
     */
    protected function handleCommand(string $chatId, string $text)
    {
        // Удаляем символ "/" из начала команды
        $command = strtolower(explode(' ', trim($text))[0]);
        
        switch ($command) {
            case '/start':
                $this->sendWelcomeMessage($chatId);
                break;
                
            case '/webapp':
            case '/app':
                $this->sendOpenAppMessage($chatId);
                break;
                
            default:
                // Любая неизвестная команда будет перенаправлять на открытие приложения
                $this->sendWelcomeMessage($chatId);
                break;
        }
    }
    
    /**
     * Обработка CallbackQuery
     */
    protected function handleCallbackQuery(array $callbackQuery)
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        
        // Подтверждение получения callbackQuery
        $this->telegramService->callApi('answerCallbackQuery', [
            'callback_query_id' => $callbackQuery['id'],
        ]);
        
        // Показываем сообщение с открытием приложения
        $this->sendOpenAppMessage($chatId);
    }
    
    /**
     * Отправка приветственного сообщения
     */
    protected function sendWelcomeMessage(string $chatId)
    {
        $text = "👋 *Добро пожаловать в Fizio Fitness Bot!*\n\n";
        $text .= "Нажмите на кнопку ниже, чтобы открыть приложение:";
        
        // Создаем клавиатуру только с одной кнопкой
        $keyboard = [
            ['Открыть приложение'],
        ];
        
        $this->telegramService->sendMessage($chatId, $text, [
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]),
        ]);
    }
    
    /**
     * Отправка сообщения с кнопкой открытия приложения
     */
    protected function sendOpenAppMessage(string $chatId)
    {
        $this->telegramService->sendMessage($chatId, 'Нажмите на кнопку ниже, чтобы открыть приложение:', [
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'Открыть Fizio',
                            'web_app' => ['url' => config('telegram.webapp.url')]
                        ]
                    ]
                ]
            ])
        ]);
    }
    
    /**
     * Страница WebApp для Telegram
     */
    public function webApp()
    {
        // Возвращаем представление SPA вместо редиректа
        return view('spa');
    }
} 