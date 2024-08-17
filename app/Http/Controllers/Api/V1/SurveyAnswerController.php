<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyAnswer\StoreSurveyAnswerRequest;
use App\Http\Requests\SurveyAnswer\UpdateSurveyAnswerRequest;
use App\Jobs\GetFinancial;
use App\Jobs\GetPersonality;
use App\Models\SurveyAnswer;
use App\Models\SurveyResult;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class SurveyAnswerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SurveyAnswer::class, 'survey_answer');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, SurveyResult $surveyResult): JsonResponse
    {
        $surveyAnswers = QueryBuilder::for(SurveyAnswer::class)
            ->allowedIncludes([
                AllowedInclude::relationship('result', 'surveyResult'),
                AllowedInclude::relationship('question', 'surveyQuestion'),
            ])
            ->allowedFilters([
                'question_id',
            ])
            ->allowedSorts([
                'question_id',
                'answer',
            ])
            ->where('result_id', $surveyResult->id)
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::paginatedCollection('survey_answers', $surveyAnswers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSurveyAnswerRequest $request, SurveyResult $surveyResult): JsonResponse
    {
        $data = $request->validated();

        $surveyType = $surveyResult->survey->type;

        if (array_is_list($data)) {
            $surveyAnswers = [];

            foreach ($data as $datum) {
                $datum['result_id'] = $surveyResult->id;
                $surveyAnswers[] = $datum;
            }

            $surveyAnswers = SurveyAnswer::createMany($surveyAnswers);

            if ($surveyType === 'PERSONALITY') {
                dispatch(new GetPersonality($surveyResult));
            } elseif ($surveyType === 'FINANCIAL') {
                dispatch(new GetFinancial($surveyResult));
            }

            return ResponseFormatter::collection('survey_answers', $surveyAnswers, 201);
        } else {
            $data['result_id'] = $surveyResult->id;

            $surveyAnswer = SurveyAnswer::create($data);

            if ($surveyType === 'PERSONALITY') {
                dispatch(new GetPersonality($surveyResult));
            } elseif ($surveyType === 'FINANCIAL') {
                dispatch(new GetFinancial($surveyResult));
            }

            return ResponseFormatter::singleton('survey_answer', $surveyAnswer, 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SurveyAnswer $surveyAnswer): JsonResponse
    {
        $surveyAnswer = QueryBuilder::for(SurveyAnswer::where('id', $surveyAnswer->id))
            ->allowedIncludes([
                AllowedInclude::relationship('result', 'surveyResult'),
                AllowedInclude::relationship('question', 'surveyQuestion'),
            ])
            ->firstOrFail();

        return ResponseFormatter::singleton('survey_answer', $surveyAnswer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSurveyAnswerRequest $request, SurveyAnswer $surveyAnswer): JsonResponse
    {
        $surveyAnswer->update($request->validated());

        $surveyResult = $surveyAnswer->surveyResult;
        $surveyType = $surveyResult->survey->type;

        if ($surveyType === 'PERSONALITY') {
            dispatch(new GetPersonality($surveyResult));
        } elseif ($surveyType === 'FINANCIAL') {
            dispatch(new GetFinancial($surveyResult));
        }

        return ResponseFormatter::singleton('survey_answer', $surveyAnswer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SurveyAnswer $surveyAnswer): JsonResponse
    {
        $surveyAnswer->delete();

        return ResponseFormatter::singleton('survey_answer', $surveyAnswer);
    }
}
