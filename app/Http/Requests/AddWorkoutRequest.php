<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddWorkoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workout_id' => 'required|uuid|exists:workouts,id',
        ];
    }
} 