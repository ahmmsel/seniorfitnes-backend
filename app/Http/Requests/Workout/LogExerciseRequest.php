<?php

namespace App\Http\Requests\Workout;

use Illuminate\Foundation\Http\FormRequest;

class LogExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reps' => 'required|integer|min:1',
            'weight' => 'nullable|numeric|min:0',
        ];
    }
}
