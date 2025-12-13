<?php

namespace App\Http\Requests\Workout;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled via PlanPolicy in controller
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'sometimes|string',
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'workout_ids' => 'sometimes|array',
            'workout_ids.*' => 'integer|exists:workouts,id',
            'meal_ids' => 'sometimes|array',
            'meal_ids.*' => 'integer|exists:meals,id',
        ];
    }

    public function messages(): array
    {
        return [
            'workout_ids.*.exists' => 'One or more workout IDs are invalid.',
            'meal_ids.*.exists' => 'One or more meal IDs are invalid.',
        ];
    }
}
