<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'email' => ['sometimes', 'email', 'unique:users'],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
            'picture' => ['sometimes', 'nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'role' => [
                Rule::when(
                    $this->user()->role === 'ADMIN',
                    ['sometimes', 'string', 'in:USER,ADMIN'],
                    ['prohibited'],
                ),
            ],
        ];
    }
}
