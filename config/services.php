<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI API
    |--------------------------------------------------------------------------
    |
    | Здесь вы можете указать настройки для использования API OpenAI.
    |
    */
    'openai' => [
        'enabled' => env('OPENAI_ENABLED', false),
        'api_key' => env('OPENAI_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | GPTunnel API
    |--------------------------------------------------------------------------
    |
    | Настройки для использования API GPTunnel с Gemini и другими моделями.
    |
    */
    'gptunnel' => [
        'enabled' => env('GPTUNNEL_ENABLED', false),
        'api_key' => env('GPTUNNEL_API_KEY'),
        'model' => env('GPTUNNEL_MODEL', 'gemini-2.5-flash'),
    ],

];
