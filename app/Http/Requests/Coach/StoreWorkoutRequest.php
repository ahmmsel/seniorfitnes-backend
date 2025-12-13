<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // controller verifies coach ownership
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:workouts,slug',
            'description' => 'nullable|string',
            'exercise_ids' => 'nullable|array',
            'exercise_ids.*' => 'integer|exists:exercises,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ];
    }
}
