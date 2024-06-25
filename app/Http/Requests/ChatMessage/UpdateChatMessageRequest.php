<?php

namespace App\Http\Requests\ChatMessage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChatMessageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'chat_session_id' => ['sometimes', 'uuid', 'exists:chat_sessions,id'],
            'sender' => ['sometimes', 'string', 'in:USER,BOT'],
            'message' => ['sometimes', 'string'],
        ];
    }
}
