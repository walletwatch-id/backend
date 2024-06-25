<?php

namespace App\Http\Requests\PaylaterHotline;

use Illuminate\Foundation\Http\FormRequest;

class StorePaylaterHotlineRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'paylater_id' => ['required', 'uuid', 'exists:paylaters,id'],
            'hotline_id' => ['required', 'uuid', 'exists:hotlines,id'],
        ];
    }
}
