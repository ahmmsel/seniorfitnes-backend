<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;

class CoachProfileStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'nullable|string',
            'specialty' => 'required|in:nutrition,workout,both',
            'years_of_experience' => 'required|numeric|min:0',
            'nutrition_price' => 'required|numeric|min:0',
            'workout_price' => 'required|numeric|min:0',
            'full_package_price' => 'required|numeric|min:0',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ];
    }
}
