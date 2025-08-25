<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    |
    | Здесь настраиваются параметры для Telegram бота
    |
    */

    'default' => env('TELEGRAM_BOT', 'main'),

    'bots' => [
        'main' => [
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'username' => env('TELEGRAM_BOT_USERNAME'),
            'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Telegram WebApp Validation
    |--------------------------------------------------------------------------
    |
    | Настройки для валидации данных WebApp
    |
    */

    'webapp' => [
        'validation_timeout' => env('TELEGRAM_VALIDATION_TIMEOUT', 86400), // 24 часа в секундах
        'use_ed25519' => env('TELEGRAM_USE_ED25519', true), // Использовать новую валидацию Ed25519
        'fallback_to_hash' => env('TELEGRAM_FALLBACK_TO_HASH', true), // Fallback к старой валидации
    ],

    /*
    |--------------------------------------------------------------------------
    | Public Key for Ed25519 Validation
    |--------------------------------------------------------------------------
    |
    | Публичный ключ бота для валидации Ed25519 подписи
    | Можно получить через getWebhookInfo API
    |
    */

    'public_key' => env('TELEGRAM_PUBLIC_KEY', null),

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    |
    | Настройки для API запросов
    |
    */

    'api' => [
        'timeout' => env('TELEGRAM_API_TIMEOUT', 30),
        'retry_attempts' => env('TELEGRAM_API_RETRY_ATTEMPTS', 3),
    ],
]; 