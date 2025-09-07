<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request для валидации данных упражнений V2
 */
class WorkoutExerciseV2Request extends FormRequest
{
    /**
     * Определить, авторизован ли пользователь для выполнения запроса
     */
    public function authorize(): bool
    {
        return true; // В админке всегда разрешено
    }

    /**
     * Получить правила валидации для запроса
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $exerciseId = $this->route('workoutExerciseV2') ?? $this->route('record');
        
        return [
            'program_id' => ['required', 'exists:workout_programs_v2,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('workout_exercises_v2', 'slug')->ignore($exerciseId)
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            
            // Валидация видео файлов
            'video_file' => [
                'nullable',
                'file',
                'mimes:mp4,avi,mov,wmv,webm',
                'max:102400' // 100MB
            ],
            'thumbnail_file' => [
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,webp',
                'max:5120' // 5MB
            ],
            
            // Валидация URL (альтернатива файлам)
            'video_url' => [
                'nullable',
                'url',
                'max:500',
                'regex:/^https?:\/\/(www\.)?(youtube\.com|youtu\.be|vimeo\.com|dailymotion\.com|twitch\.tv)/i'
            ],
            'thumbnail_url' => [
                'nullable',
                'url',
                'max:500',
                'regex:/^https?:\/\/.*\.(jpg|jpeg|png|gif|webp)$/i'
            ],
            
            // Валидация характеристик упражнения
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'sets' => ['required', 'integer', 'min:1', 'max:50'],
            'reps' => ['required', 'integer', 'min:1', 'max:1000'],
            'rest_seconds' => ['nullable', 'integer', 'min:0', 'max:600'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            
            // Валидация JSON полей
            'equipment_needed' => ['nullable', 'array'],
            'equipment_needed.*' => ['string', 'max:100'],
            'muscle_groups' => ['nullable', 'array'],
            'muscle_groups.*' => ['string', 'max:100'],
            
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Получить сообщения валидации
     */
    public function messages(): array
    {
        return [
            'program_id.required' => 'Необходимо выбрать программу тренировок',
            'program_id.exists' => 'Выбранная программа не существует',
            
            'name.required' => 'Название упражнения обязательно',
            'name.max' => 'Название не должно превышать 255 символов',
            
            'slug.required' => 'Slug обязателен',
            'slug.unique' => 'Такой slug уже существует',
            
            'video_file.file' => 'Видео должно быть файлом',
            'video_file.mimes' => 'Видео должно быть в формате MP4, AVI, MOV, WMV или WebM',
            'video_file.max' => 'Размер видео не должен превышать 100MB',
            
            'thumbnail_file.file' => 'Превью должно быть файлом',
            'thumbnail_file.mimes' => 'Превью должно быть в формате JPEG, PNG или WebP',
            'thumbnail_file.max' => 'Размер превью не должен превышать 5MB',
            
            'video_url.url' => 'URL видео должен быть корректной ссылкой',
            'video_url.regex' => 'Поддерживаются только YouTube, Vimeo, Dailymotion и Twitch',
            
            'thumbnail_url.url' => 'URL превью должен быть корректной ссылкой',
            'thumbnail_url.regex' => 'Превью должно быть изображением (JPG, PNG, GIF, WebP)',
            
            'sets.required' => 'Количество подходов обязательно',
            'sets.min' => 'Минимум 1 подход',
            'sets.max' => 'Максимум 50 подходов',
            
            'reps.required' => 'Количество повторений обязательно',
            'reps.min' => 'Минимум 1 повторение',
            'reps.max' => 'Максимум 1000 повторений',
        ];
    }

    /**
     * Получить пользовательские имена атрибутов
     */
    public function attributes(): array
    {
        return [
            'program_id' => 'программа',
            'name' => 'название',
            'slug' => 'slug',
            'description' => 'описание',
            'instructions' => 'инструкции',
            'video_file' => 'видео файл',
            'thumbnail_file' => 'превью файл',
            'video_url' => 'URL видео',
            'thumbnail_url' => 'URL превью',
            'duration_seconds' => 'длительность',
            'sets' => 'подходы',
            'reps' => 'повторения',
            'rest_seconds' => 'отдых',
            'weight_kg' => 'вес',
            'sort_order' => 'порядок',
            'equipment_needed' => 'оборудование',
            'muscle_groups' => 'группы мышц',
            'is_active' => 'активность',
        ];
    }
}
