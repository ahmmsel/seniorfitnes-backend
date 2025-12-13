<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;

class CoachProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'nullable|string',
            'specialty' => 'nullable|in:nutrition,workout,both',
            'years_of_experience' => 'nullable|numeric|min:0',
            'nutrition_price' => 'nullable|numeric|min:0',
            'workout_price' => 'nullable|numeric|min:0',
            'full_package_price' => 'nullable|numeric|min:0',
            'profile_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
        ];
    }
}
