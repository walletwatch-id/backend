<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyResult\StoreSurveyResultRequest;
use App\Http\Requests\SurveyResult\UpdateSurveyResultRequest;
use App\Models\Survey;
use App\Models\SurveyResult;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class SurveyResultController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SurveyResult::class, 'survey_result');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Survey $survey): JsonResponse
    {
        $surveyResults = QueryBuilder::for(SurveyResult::class)
            ->allowedIncludes([
                'user',
                'survey',
                AllowedInclude::relationship('answer', 'surveyResultAnswers'),
            ]);

        if ($request->user()->role === 'ADMIN') {
            $surveyResults = $surveyResults
                ->allowedFilters([
                    'user_id',
                    'type',
                ])
                ->allowedSorts([
                    'user_id',
                    'result',
                    'type',
                ]);
        } else {
            $surveyResults = $surveyResults
                ->allowedFilters([
                    'type',
                ])
                ->allowedSorts([
                    'result',
                    'type',
                ])
                ->where('user_id', $request->user()->id);
        }

        $surveyResults = $surveyResults
            ->where('survey_id', $survey->id)
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::collection('survey_results', $surveyResults);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSurveyResultRequest $request, Survey $survey): JsonResponse
    {
        $surveyResult = new SurveyResult($request->validated());
        $surveyResult->fill([
            'survey_id' => $survey->id,
        ]);

        if ($request->user()->role !== 'ADMIN') {
            $surveyResult->fill([
                'user_id' => $request->user()->id,
            ]);
        }

        $surveyResult->save();

        return ResponseFormatter::singleton('survey_result', $surveyResult, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SurveyResult $surveyResult): JsonResponse
    {
        $surveyResult = QueryBuilder::for(SurveyResult::where('id', $surveyResult->id))
            ->allowedIncludes([
                'user',
                'survey',
                AllowedInclude::relationship('answer', 'surveyResultAnswers'),
            ])
            ->firstOrFail();

        return ResponseFormatter::singleton('survey_result', $surveyResult);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSurveyResultRequest $request, SurveyResult $surveyResult): JsonResponse
    {
        $surveyResult->fill($request->validated());
        $surveyResult->save();

        return ResponseFormatter::singleton('survey_result', $surveyResult);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SurveyResult $surveyResult): JsonResponse
    {
        $surveyResult->delete();

        return ResponseFormatter::singleton('survey_result', $surveyResult);
    }
}
