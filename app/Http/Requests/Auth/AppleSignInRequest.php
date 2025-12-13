<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AppleSignInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identity_token' => ['required_without:authorization_code', 'string'],
            'authorization_code' => ['required_without:identity_token', 'string'],
            'full_name' => ['nullable', 'array'],
            'full_name.given_name' => ['nullable', 'string', 'max:100'],
            'full_name.family_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'locale' => ['nullable', 'string', 'max:10'],
        ];
    }
}
