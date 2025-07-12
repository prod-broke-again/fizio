<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddMealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'calories' => 'required|numeric|min:0',
            'type' => 'required|string|in:breakfast,lunch,dinner,snack',
            'time' => 'nullable|date_format:H:i',
            'proteins' => 'nullable|numeric|min:0',
            'fats' => 'nullable|numeric|min:0',
            'carbs' => 'nullable|numeric|min:0',
            'date' => 'nullable|date',
            'food_id' => 'nullable|string|max:255',
            'completed' => 'nullable|boolean',
        ];
    }
}
