<?php

namespace App\Http\Requests\SurveyResultAnswer;

use App\Models\SurveyResultAnswer;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSurveyResultAnswerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $surveyId = SurveyResultAnswer::find($this->route('survey_result_answer'))->surveyResult->survey_id;

        return [
            'question_id' => [
                'sometimes',
                'uuid',
                Rule::exists('survey_questions', 'id')->where(function (Builder $query) use ($surveyId) {
                    $query->where('survey_id', $surveyId);
                }),
            ],
            'answer' => ['sometimes', 'string'],
        ];
    }
}
