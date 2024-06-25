<?php

namespace App\Http\Requests\ChatSession;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChatSessionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'uuid', 'exists:users,id'],
            'title' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
