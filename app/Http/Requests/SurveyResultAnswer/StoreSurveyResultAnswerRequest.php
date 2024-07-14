<?php

namespace App\Http\Requests\SurveyResultAnswer;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyResultAnswerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question_id' => ['required', 'uuid', 'exists:survey_questions,id'],
            'answer' => ['required', 'string'],
        ];
    }
}
