<?php

namespace App\Http\Requests\Paylater;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaylaterRequest extends FormRequest
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
            'picture' => ['sometimes', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
