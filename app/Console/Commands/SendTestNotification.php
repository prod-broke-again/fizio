<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Models\User;
use App\Models\Notification;
use App\Events\NotificationUpdated;
use App\Services\TelegramService;

class SendTestNotification extends Command
{
    protected $signature = 'notification:test {message?}';
    protected $description = 'Отправляет тестовое уведомление всем пользователям';

    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    public function handle()
    {
        $message = $this->argument('message') ?? 'Это тестовое уведомление!';

        try {
            $this->info("Отправка уведомлений всем пользователям...");
            
            // Получаем всех пользователей
            $users = User::all();
            
            foreach ($users as $user) {
                // Создаем уведомление в базе данных
                $notification = Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Тестовое уведомление',
                    'message' => $message,
                    'type' => 'system',
                    'read' => false
                ]);

                // Отправляем уведомление через Redis
                $redisNotification = [
                    'id' => $notification->id,
                    'user_id' => $notification->user_id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'read' => $notification->read,
                    'created_at' => $notification->created_at->toIso8601String(),
                    'updated_at' => $notification->updated_at->toIso8601String()
                ];
                
                $this->info("Отправка уведомления пользователю {$user->id}...");
                $this->info("Данные: " . json_encode($redisNotification, JSON_UNESCAPED_UNICODE));
                
                Redis::publish('notifications', json_encode($redisNotification));
                
                // Отправляем событие об обновлении уведомлений
                event(new NotificationUpdated($notification));

                // Если пользователь зарегистрирован через Telegram, отправляем уведомление в Telegram
                if ($user->hasConnectedTelegram()) {
                    $this->info("Отправка уведомления в Telegram пользователю {$user->telegram_id}...");
                    
                    $telegramMessage = "*{$notification->title}*\n\n{$notification->message}";
                    
                    $this->telegramService->sendMessage(
                        $user->telegram_id,
                        $telegramMessage,
                        ['parse_mode' => 'Markdown']
                    );
                }
            }
            
            $this->info("Уведомления успешно отправлены всем пользователям");
            
        } catch (\Exception $e) {
            $this->error("Ошибка при отправке уведомлений: " . $e->getMessage());
            $this->error("Трейс: " . $e->getTraceAsString());
        }
    }
} 