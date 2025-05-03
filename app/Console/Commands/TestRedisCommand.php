<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class TestRedisCommand extends Command
{
    protected $signature = 'test:redis';
    protected $description = 'Test Redis pub/sub functionality';

    public function handle()
    {
        $message = json_encode([
            'test' => true,
            'time' => now()->toDateTimeString()
        ]);

        $result = Redis::publish('chat:messages', $message);

        $this->info("Сообщение отправлено через Redis, результат: $result");
        $this->info("Сообщение: $message");
        
        $this->info("Настройки Redis:");
        $this->info("Host: " . config('database.redis.default.host'));
        $this->info("Port: " . config('database.redis.default.port'));
        $this->info("DB: " . config('database.redis.default.database'));
        $this->info("Client: " . config('database.redis.client'));
        
        return 0;
    }
} 