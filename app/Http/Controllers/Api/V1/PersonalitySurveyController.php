<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Survey\StoreSurveyRequest;
use App\Http\Requests\Survey\UpdateSurveyRequest;
use App\Models\Survey;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class PersonalitySurveyController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Survey::class, 'survey');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $personalitySurveys = QueryBuilder::for(Survey::class)
            ->allowedIncludes([
                AllowedInclude::relationship('questions', 'surveyQuestions'),
            ]);

        if ($request->user()->role === 'ADMIN') {
            $personalitySurveys = $personalitySurveys
                ->allowedFilters([
                    'name',
                    'is_active',
                ])
                ->allowedSorts([
                    'name',
                    'is_active',
                ])
                ->where('type', 'PERSONALITY');
        } else {
            $personalitySurveys = $personalitySurveys
                ->where('type', 'PERSONALITY')
                ->where('is_active', true);
        }

        $personalitySurveys = $personalitySurveys->paginate($request->query('per_page', 10));

        return ResponseFormatter::collection('personality_surveys', $personalitySurveys);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSurveyRequest $request): JsonResponse
    {
        $personalitySurvey = new Survey($request->validated());
        $personalitySurvey->type = 'PERSONALITY';

        $personalitySurvey->save();

        return ResponseFormatter::singleton('personality_survey', $personalitySurvey, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Survey $survey): JsonResponse
    {
        $personalitySurvey = QueryBuilder::for(Survey::where('id', $survey->id)
            ->where('type', 'PERSONALITY')
        )
            ->allowedIncludes([
                AllowedInclude::relationship('questions', 'surveyQuestions'),
            ])
            ->firstOrFail();

        return ResponseFormatter::singleton('personality_survey', $personalitySurvey);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSurveyRequest $request, Survey $survey): JsonResponse
    {
        $survey->fill($request->validated());
        $survey->save();

        return ResponseFormatter::singleton('personality_survey', $survey);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Survey $survey): JsonResponse
    {
        $survey->delete();

        return ResponseFormatter::singleton('personality_survey', $survey);
    }
}
