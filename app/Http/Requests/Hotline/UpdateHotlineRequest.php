<?php

namespace App\Http\Requests\Hotline;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHotlineRequest extends FormRequest
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
            'type' => ['sometimes', 'string', 'in:EMAIL,PHONE,URL'],
            'hotline' => ['sometimes', 'string'],
        ];
    }
}
