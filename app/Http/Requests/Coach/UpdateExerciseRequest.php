<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ];
    }
}
