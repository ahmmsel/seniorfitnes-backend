<?php

namespace App\Http\Requests\Trainee;

use Illuminate\Foundation\Http\FormRequest;

class TraineeProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'height' => 'nullable|numeric|min:50|max:300',
            'weight' => 'nullable|numeric|min:20|max:500',
            'goal' => 'nullable|in:lose_weight,build_muscle,improve_cardio,maintain_fitness',
            'level' => 'nullable|in:sedentary,lightly_active,active,very_active',
            'body_type' => 'nullable|in:underweight,normal,overweight,obese',
            'profile_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
        ];
    }
}
