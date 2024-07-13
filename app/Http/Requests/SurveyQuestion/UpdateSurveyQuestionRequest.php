<?php

namespace App\Http\Requests\SurveyQuestion;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSurveyQuestionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question' => ['sometimes', 'string'],
            'type' => ['sometimes', 'string', 'in:STRING,INTEGER,LIKERT5,LIKERT7'],
        ];
    }
}
