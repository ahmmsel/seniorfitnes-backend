<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->coachProfile !== null;
    }

    public function rules(): array
    {
        return [
            'profile_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ];
    }
}
