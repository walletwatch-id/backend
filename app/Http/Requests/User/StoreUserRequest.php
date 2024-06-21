<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'picture' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'role' => [
                Rule::when(
                    $this->user()->role === 'ADMIN',
                    ['nullable', 'string', 'in:USER,ADMIN'],
                    ['prohibited'],
                ),
            ],
        ];
    }
}
