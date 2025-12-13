<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class StartChatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'participant_id' => ['required', 'integer', 'exists:users,id'],
            'participant_type' => ['required', 'string', 'in:coach,trainee'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'participant_id.required' => 'Participant ID is required.',
            'participant_id.exists' => 'The selected participant does not exist.',
            'participant_type.required' => 'Participant type is required.',
            'participant_type.in' => 'Participant type must be either coach or trainee.',
        ];
    }
}
