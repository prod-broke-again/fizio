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
            // Отправляем ссылку на WebApp
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
        } elseif ($text === 'Мой профиль') {
            $this->telegramService->sendMessage($chatId, 'Ваш профиль в разработке. Скоро здесь появится информация о вашем прогрессе!');
        } elseif ($text === 'Статистика') {
            $this->telegramService->sendMessage($chatId, 'Статистика в разработке. Скоро здесь появятся данные о ваших тренировках!');
        } elseif ($text === 'Тренировки') {
            $this->telegramService->sendMessage($chatId, 'Раздел тренировок в разработке. Скоро здесь появится расписание ваших занятий!');
        } elseif ($text === 'Помощь') {
            $this->sendHelpMessage($chatId);
        } else {
            // Неизвестное сообщение
            $this->telegramService->sendMessage($chatId, 'Я не понимаю эту команду. Используйте кнопки меню или отправьте /help для получения справки.');
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
                break;
                
            case '/profile':
                $this->telegramService->sendMessage($chatId, 'Ваш профиль в разработке. Скоро здесь появится информация о вашем прогрессе!');
                break;
                
            case '/stats':
                $this->telegramService->sendMessage($chatId, 'Статистика в разработке. Скоро здесь появятся данные о ваших тренировках!');
                break;
                
            case '/workout':
                $this->telegramService->sendMessage($chatId, 'У вас нет запланированных тренировок. Воспользуйтесь приложением, чтобы добавить тренировку.');
                break;
                
            case '/help':
                $this->sendHelpMessage($chatId);
                break;
                
            default:
                $this->telegramService->sendMessage($chatId, 'Неизвестная команда. Отправьте /help для получения справки.');
                break;
        }
    }
    
    /**
     * Обработка CallbackQuery
     */
    protected function handleCallbackQuery(array $callbackQuery)
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $data = $callbackQuery['data'];
        
        // Обработка различных callbackQuery данных
        // ...
        
        // Подтверждение получения callbackQuery
        $this->telegramService->callApi('answerCallbackQuery', [
            'callback_query_id' => $callbackQuery['id'],
        ]);
    }
    
    /**
     * Отправка приветственного сообщения
     */
    protected function sendWelcomeMessage(string $chatId)
    {
        $text = "👋 *Добро пожаловать в Fizio Fitness Bot!*\n\n";
        $text .= "Я ваш персональный фитнес-помощник. С моей помощью вы можете:\n";
        $text .= "• Открыть фитнес-приложение\n";
        $text .= "• Просматривать свой профиль\n";
        $text .= "• Получать статистику тренировок\n";
        $text .= "• Следить за расписанием тренировок\n\n";
        $text .= "Используйте кнопки меню или отправьте /help для получения справки.";
        
        // Создаем клавиатуру
        $keyboard = [
            ['Открыть приложение'],
            ['Мой профиль', 'Статистика'],
            ['Тренировки', 'Помощь'],
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
     * Отправка справочного сообщения
     */
    protected function sendHelpMessage(string $chatId)
    {
        $text = "*Доступные команды:*\n\n";
        $text .= "/start - Начать работу с ботом\n";
        $text .= "/webapp - Открыть веб-приложение\n";
        $text .= "/profile - Просмотреть ваш профиль\n";
        $text .= "/stats - Получить статистику тренировок\n";
        $text .= "/workout - Информация о следующей тренировке\n";
        $text .= "/help - Получить эту справку\n\n";
        $text .= "Вы также можете использовать кнопки меню для быстрого доступа к функциям.";
        
        $this->telegramService->sendMessage($chatId, $text, [
            'parse_mode' => 'Markdown',
        ]);
    }
    
    /**
     * Страница WebApp для Telegram
     */
    public function webApp()
    {
        return view('telegram.webapp');
    }
} 