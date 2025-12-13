<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchasePlanRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'coach_profile_id' => 'required|integer|exists:coach_profiles,id',
            'plan_type' => 'required|string|in:nutrition,workout,full_package',
        ];
    }
}
