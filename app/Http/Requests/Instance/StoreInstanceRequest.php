<?php

namespace App\Http\Requests\Instance;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstanceRequest extends FormRequest
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
            'logo' => ['required', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
