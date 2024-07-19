<?php

namespace App\Http\Requests\Transaction;

use DateTimeInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
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
                    ['required', 'uuid', 'exists:users,id'],
                    ['prohibited'],
                ),
            ],
            'paylater_id' => ['required', 'uuid', 'exists:paylaters,id'],
            'monthly_installment' => ['required', 'integer', 'min:1'],
            'period' => ['required', 'integer', 'min:1'],
            'first_installment_datetime' => ['required', 'date_format:'.DateTimeInterface::ATOM],
            'transaction_datetime' => ['required', 'date_format:'.DateTimeInterface::ATOM],
        ];
    }
}
