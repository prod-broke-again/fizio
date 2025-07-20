<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Обновляем ChatController для использования очередей
        if (config('queue.default') !== 'sync') {
            // Используем асинхронную очередь для обработки сообщений чата
            $chatController = \App\Http\Controllers\API\ChatController::class;

            app()->when($chatController)
                ->needs('$useQueue')
                ->give(true);
        }
    }
}
