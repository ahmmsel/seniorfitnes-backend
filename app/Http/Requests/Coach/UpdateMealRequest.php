<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMealRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'date' => ['sometimes', 'nullable', 'date'],
            'type' => ['sometimes', 'nullable', 'string'],
            'calories' => ['sometimes', 'nullable', 'numeric'],
            'protein' => ['sometimes', 'nullable', 'numeric'],
            'carbs' => ['sometimes', 'nullable', 'numeric'],
            'fats' => ['sometimes', 'nullable', 'numeric'],
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }
}
