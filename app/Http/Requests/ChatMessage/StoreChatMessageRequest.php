<?php

namespace App\Http\Requests\ChatMessage;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatMessageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'chat_session_id' => ['required', 'uuid', 'exists:chat_sessions,id'],
            'sender' => ['required', 'string', 'in:USER,BOT'],
            'message' => ['required', 'string'],
        ];
    }
}
