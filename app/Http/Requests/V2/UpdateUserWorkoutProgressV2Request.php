<?php

declare(strict_types=1);

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserWorkoutProgressV2Request extends FormRequest
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
            'completed_at' => [
                'nullable',
                'date',
                'before_or_equal:now'
            ],
            'duration_seconds' => [
                'nullable',
                'integer',
                'min:0',
                'max:86400' // Максимум 24 часа
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ],
        ];
    }
    
    /**
     * Получить сообщения об ошибках валидации
     */
    public function messages(): array
    {
        return [
            'completed_at.date' => 'Дата завершения должна быть корректной датой',
            'completed_at.before_or_equal' => 'Дата завершения не может быть в будущем',
            'duration_seconds.integer' => 'Длительность должна быть целым числом',
            'duration_seconds.min' => 'Длительность не может быть отрицательной',
            'duration_seconds.max' => 'Длительность не может превышать 24 часа',
            'notes.max' => 'Заметки не могут превышать 1000 символов',
        ];
    }
    
    /**
     * Получить атрибуты для сообщений об ошибках
     */
    public function attributes(): array
    {
        return [
            'completed_at' => 'дата завершения',
            'duration_seconds' => 'длительность',
            'notes' => 'заметки',
        ];
    }
}
