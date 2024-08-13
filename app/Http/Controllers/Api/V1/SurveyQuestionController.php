<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyQuestion\StoreSurveyQuestionRequest;
use App\Http\Requests\SurveyQuestion\UpdateSurveyQuestionRequest;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class SurveyQuestionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SurveyQuestion::class, 'survey_question');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Survey $survey): JsonResponse
    {
        $surveyQuestions = QueryBuilder::for(SurveyQuestion::class)
            ->allowedIncludes([
                'survey',
            ])
            ->allowedFilters([
                'type',
            ])
            ->allowedSorts([
                'question',
                'type',
            ])
            ->where('survey_id', $survey->id)
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::paginatedCollection('survey_questions', $surveyQuestions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSurveyQuestionRequest $request, Survey $survey): JsonResponse
    {
        $surveyQuestion = new SurveyQuestion($request->validated());
        $surveyQuestion->fill([
            'survey_id' => $survey->id,
        ]);
        $surveyQuestion->save();

        return ResponseFormatter::singleton('survey_question', $surveyQuestion, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SurveyQuestion $surveyQuestion): JsonResponse
    {
        $surveyQuestion = QueryBuilder::for(SurveyQuestion::where('id', $surveyQuestion->id))
            ->allowedIncludes([
                'survey',
            ])
            ->firstOrFail();

        return ResponseFormatter::singleton('survey_question', $surveyQuestion);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSurveyQuestionRequest $request, SurveyQuestion $surveyQuestion): JsonResponse
    {
        $surveyQuestion->fill($request->validated());
        $surveyQuestion->save();

        return ResponseFormatter::singleton('survey_question', $surveyQuestion);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SurveyQuestion $surveyQuestion): JsonResponse
    {
        $surveyQuestion->delete();

        return ResponseFormatter::singleton('survey_question', $surveyQuestion);
    }
}
