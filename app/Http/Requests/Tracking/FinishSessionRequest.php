<?php

namespace App\Http\Requests\Tracking;

use Illuminate\Foundation\Http\FormRequest;

class FinishSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->traineeProfile !== null;
    }

    public function rules(): array
    {
        return [
            'session_id' => 'required|exists:tracking_sessions,id',
            'distance' => 'required|numeric|min:0',
            'time_seconds' => 'required|integer|min:0',
            'bpm' => 'nullable|integer|min:0|max:250',
            'steps' => 'nullable|integer|min:0',
            'pace' => 'nullable|numeric|min:0',
            'calories' => 'nullable|numeric|min:0',
        ];
    }
}
