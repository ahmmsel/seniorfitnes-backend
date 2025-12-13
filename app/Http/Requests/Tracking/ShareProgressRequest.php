<?php

namespace App\Http\Requests\Tracking;

use Illuminate\Foundation\Http\FormRequest;

class ShareProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->traineeProfile !== null;
    }

    public function rules(): array
    {
        return [
            'description' => 'nullable|string|max:1000',
        ];
    }
}
