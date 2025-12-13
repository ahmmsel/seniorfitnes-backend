<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePlanFromPurchaseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'trainee_plan_id' => 'required|integer|exists:trainee_plan,id',
            'type' => 'required|string',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'workout_ids' => 'sometimes|array',
            'workout_ids.*' => 'integer|exists:workouts,id',
            'meal_ids' => 'sometimes|array',
            'meal_ids.*' => 'integer|exists:meals,id',
        ];
    }
}
