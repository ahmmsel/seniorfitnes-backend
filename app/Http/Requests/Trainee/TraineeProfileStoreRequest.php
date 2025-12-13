<?php

namespace App\Http\Requests\Trainee;

use Illuminate\Foundation\Http\FormRequest;

class TraineeProfileStoreRequest extends FormRequest
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
            'height' => 'required|numeric|min:50|max:300',
            'weight' => 'required|numeric|min:20|max:500',
            'goal' => 'required|in:lose_weight,build_muscle,improve_cardio,maintain_fitness',
            'level' => 'required|in:sedentary,lightly_active,active,very_active',
            'body_type' => 'required|in:underweight,normal,overweight,obese',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ];
    }
}
