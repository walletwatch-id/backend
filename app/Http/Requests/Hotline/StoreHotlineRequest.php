<?php

namespace App\Http\Requests\Hotline;

use Illuminate\Foundation\Http\FormRequest;

class StoreHotlineRequest extends FormRequest
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
            'type' => ['required', 'string', 'in:EMAIL,PHONE,URL'],
            'hotline' => ['required', 'string'],
        ];
    }
}
