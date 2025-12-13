<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $workoutId = $this->route('workout')?->id ?? null;

        return [
            'name' => 'nullable|string|max:255',
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => 'nullable|string',
            'exercise_ids' => 'nullable|array',
            'exercise_ids.*' => 'integer|exists:exercises,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ];
    }
}
