<?php

namespace App\Http\Requests\Transaction;

use DateTimeInterface;
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
            'monthly_installment' => ['sometimes', 'integer', 'min:1'],
            'period' => ['sometimes', 'integer', 'min:1'],
            'first_installment_datetime' => ['sometimes', 'date_format:'.DateTimeInterface::ATOM],
            'transaction_datetime' => ['sometimes', 'date_format:'.DateTimeInterface::ATOM],
        ];
    }
}
