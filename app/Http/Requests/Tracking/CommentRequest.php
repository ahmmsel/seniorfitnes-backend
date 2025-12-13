<?php

namespace App\Http\Requests\Tracking;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->traineeProfile !== null;
    }

    public function rules(): array
    {
        return [
            'comment' => 'required|string|max:500',
        ];
    }
}
