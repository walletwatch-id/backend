<?php

namespace App\Http\Requests\InstanceHotline;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstanceHotlineRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'instance_id' => ['sometimes', 'uuid', 'exists:instances,id'],
            'hotline_id' => ['sometimes', 'uuid', 'exists:hotlines,id'],
        ];
    }
}
