<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Survey\StoreSurveyRequest;
use App\Http\Requests\Survey\UpdateSurveyRequest;
use App\Models\Survey;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class FinancialSurveyController extends Controller
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
        $financialSurveys = QueryBuilder::for(Survey::class)
            ->allowedIncludes([
                AllowedInclude::relationship('questions', 'surveyQuestions'),
            ]);

        if ($request->user()->role === 'ADMIN') {
            $financialSurveys = $financialSurveys
                ->allowedFilters([
                    'name',
                    AllowedFilter::exact('is_active'),
                ])
                ->allowedSorts([
                    'name',
                    'is_active',
                ])
                ->where('type', 'FINANCIAL');
        } else {
            $financialSurveys = $financialSurveys
                ->where('type', 'FINANCIAL')
                ->where('is_active', true);
        }

        $financialSurveys = $financialSurveys->paginate($request->query('per_page', 10));

        return ResponseFormatter::paginatedCollection('financial_surveys', $financialSurveys);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSurveyRequest $request): JsonResponse
    {
        $financialSurvey = new Survey($request->validated());
        $financialSurvey->type = 'FINANCIAL';

        $financialSurvey->save();

        return ResponseFormatter::singleton('financial_survey', $financialSurvey, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Survey $survey): JsonResponse
    {
        $financialSurvey = QueryBuilder::for(Survey::where('id', $survey->id)
            ->where('type', 'FINANCIAL')
        )
            ->allowedIncludes([
                AllowedInclude::relationship('questions', 'surveyQuestions'),
            ])
            ->firstOrFail();

        return ResponseFormatter::singleton('financial_survey', $financialSurvey);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSurveyRequest $request, Survey $survey): JsonResponse
    {
        $survey->fill($request->validated());
        $survey->save();

        return ResponseFormatter::singleton('financial_survey', $survey);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Survey $survey): JsonResponse
    {
        $survey->delete();

        return ResponseFormatter::singleton('financial_survey', $survey);
    }
}
