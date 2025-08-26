<?php

declare(strict_types=1);

namespace App\Http\Requests\V2;

use App\Enums\SubscriptionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreUserSubscriptionV2Request extends FormRequest
{
    /**
     * Определить, авторизован ли пользователь для выполнения запроса
     */
    public function authorize(): bool
    {
        return true; // Авторизация проверяется в middleware
    }
    
    /**
     * Получить правила валидации для запроса
     */
    public function rules(): array
    {
        return [
            'subscription_type' => [
                'required',
                new Enum(SubscriptionType::class)
            ],
        ];
    }
    
    /**
     * Получить сообщения об ошибках валидации
     */
    public function messages(): array
    {
        return [
            'subscription_type.required' => 'Тип подписки обязателен',
            'subscription_type.enum' => 'Указан недопустимый тип подписки',
        ];
    }
    
    /**
     * Получить атрибуты для сообщений об ошибках
     */
    public function attributes(): array
    {
        return [
            'subscription_type' => 'тип подписки',
        ];
    }
}
