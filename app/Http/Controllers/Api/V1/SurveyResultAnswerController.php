<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyResultAnswer\StoreSurveyResultAnswerRequest;
use App\Http\Requests\SurveyResultAnswer\UpdateSurveyResultAnswerRequest;
use App\Models\SurveyResult;
use App\Models\SurveyResultAnswer;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\Uid\Uuid;

class SurveyResultAnswerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SurveyResultAnswer::class, 'survey_result_answer');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, SurveyResult $surveyResult): JsonResponse
    {
        $surveyResultAnswers = QueryBuilder::for(SurveyResultAnswer::class)
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

        return ResponseFormatter::collection('survey_result_answers', $surveyResultAnswers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSurveyResultAnswerRequest $request, SurveyResult $surveyResult): JsonResponse
    {
        $data = $request->validated();
        if (is_array($data)) {
            $surveyResultAnswers = [];
            $timestamp = Carbon::now();

            foreach ($data as $datum) {
                $surveyResultAnswers[] = array_merge($datum, [
                    'id' => Uuid::v7(),
                    'result_id' => $surveyResult->id,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }

            SurveyResultAnswer::insert($surveyResultAnswers);

            return ResponseFormatter::unpaginatedCollection('survey_result_answers', $surveyResultAnswers, 201);
        } else {
            $surveyResultAnswer = new SurveyResultAnswer($data);
            $surveyResultAnswer->fill([
                'result_id' => $surveyResult->id,
            ]);

            $surveyResultAnswer->save();

            return ResponseFormatter::singleton('survey_result_answer', $surveyResultAnswer, 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SurveyResultAnswer $surveyResultAnswer): JsonResponse
    {
        $surveyResultAnswer = QueryBuilder::for(SurveyResultAnswer::where('id', $surveyResultAnswer->id))
            ->allowedIncludes([
                AllowedInclude::relationship('result', 'surveyResult'),
                AllowedInclude::relationship('question', 'surveyQuestion'),
            ])
            ->firstOrFail();

        return ResponseFormatter::singleton('survey_result_answer', $surveyResultAnswer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSurveyResultAnswerRequest $request, SurveyResultAnswer $surveyResultAnswer): JsonResponse
    {
        $surveyResultAnswer->fill($request->validated());
        $surveyResultAnswer->save();

        return ResponseFormatter::singleton('survey_result_answer', $surveyResultAnswer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SurveyResultAnswer $surveyResultAnswer): JsonResponse
    {
        $surveyResultAnswer->delete();

        return ResponseFormatter::singleton('survey_result_answer', $surveyResultAnswer);
    }
}
