<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class GoogleSignInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'access_token' => ['required_without:id_token', 'string'],
            'id_token' => ['required_without:access_token', 'string'],
            'authorization_code' => ['nullable', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'locale' => ['nullable', 'string', 'max:10'],
            'invite_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
