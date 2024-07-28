<?php

namespace App\Http\Requests\SurveyAnswer;

use App\Models\SurveyResult;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSurveyAnswerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $surveyId = SurveyResult::find($this->route('survey_result'))->survey_id;

        if (is_array($this->all())) {
            return [
                '*.question_id' => [
                    'required',
                    'uuid',
                    Rule::exists('survey_questions', 'id')->where(function (Builder $query) use ($surveyId) {
                        $query->where('survey_id', $surveyId);
                    }),
                ],
                '*.answer' => ['required', 'string'],
            ];
        } else {
            return [
                'question_id' => [
                    'required',
                    'uuid',
                    Rule::exists('survey_questions', 'id')->where(function (Builder $query) use ($surveyId) {
                        $query->where('survey_id', $surveyId);
                    }),
                ],
                'answer' => ['required', 'string'],
            ];
        }
    }
}