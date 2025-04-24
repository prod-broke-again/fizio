<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Telegram Bot API settings. These are used when
    | your application needs to interact with the Telegram Bot API.
    |
    */

    'default' => env('TELEGRAM_BOT_NAME', 'FizioBot'),

    'bots' => [
        'FizioBot' => [
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
            'certificate_path' => env('TELEGRAM_CERTIFICATE_PATH', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Telegram WebApp Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Telegram WebApp settings. These are used when
    | your application is opened as a WebApp within Telegram.
    |
    */

    'webapp' => [
        'url' => env('TELEGRAM_WEBAPP_URL', env('APP_URL') . '/telegram/webapp'),
    ],
]; 