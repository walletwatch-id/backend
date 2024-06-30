<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                Rule::when(
                    $this->user()->role === 'ADMIN',
                    ['sometimes', 'uuid', 'exists:users,id'],
                    ['prohibited'],
                ),
            ],
            'paylater_id' => ['sometimes', 'uuid', 'exists:paylaters,id'],
            'datetime' => ['sometimes', 'date_format:ATOM'],
        ];
    }
}
