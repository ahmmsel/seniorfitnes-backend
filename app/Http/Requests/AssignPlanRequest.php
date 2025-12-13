<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignPlanRequest extends FormRequest
{
    public function authorize()
    {
        $user = $this->user();
        if (! $user || ! method_exists($user, 'coachProfile') || is_null($user->coachProfile)) {
            return false;
        }

        // if route contains a Plan model, ensure user owns it
        $plan = $this->route('plan');
        if ($plan) {
            return $plan->coach_profile_id === $user->coachProfile->id;
        }

        return true;
    }

    public function rules()
    {
        return [
            'trainee_profile_id' => 'required|integer|exists:trainee_profiles,id',
            'items' => 'sometimes|array',
            'purchased_at' => 'sometimes|date',
        ];
    }
}
