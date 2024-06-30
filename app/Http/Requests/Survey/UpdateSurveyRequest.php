<?php

namespace App\Http\Requests\Survey;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSurveyRequest extends FormRequest
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
            'date' => ['sometimes', 'date_format:ATOM'],
        ];
    }
}
