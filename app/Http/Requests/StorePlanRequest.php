<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize()
    {
        $user = $this->user();
        // only allow if user has a coachProfile
        return $user && method_exists($user, 'coachProfile') && !is_null($user->coachProfile);
    }

    public function rules()
    {
        return [
            'type' => 'required|string',
            'title' => 'required|string',
            // price removed from plans table; coach pricing comes from their CoachProfile
            'description' => 'nullable|string',
            'workout_ids' => 'sometimes|array',
            'workout_ids.*' => 'integer|exists:workouts,id',
            'meal_ids' => 'sometimes|array',
            'meal_ids.*' => 'integer|exists:meals,id',
            // optional assign-to-trainee fields
            'trainee_profile_id' => 'sometimes|integer|exists:trainee_profiles,id',
            'items' => 'sometimes|array',
            'purchased_at' => 'sometimes|date',
        ];
    }

    /**
     * Prepare the data for validation — ensure arrays present when empty.
     */
    protected function prepareForValidation()
    {
        if ($this->has('workout_ids') && is_null($this->input('workout_ids'))) {
            $this->merge(['workout_ids' => []]);
        }
        if ($this->has('meal_ids') && is_null($this->input('meal_ids'))) {
            $this->merge(['meal_ids' => []]);
        }
    }
}
