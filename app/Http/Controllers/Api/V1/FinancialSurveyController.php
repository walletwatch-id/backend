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

class FinancialSurveyController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Survey::class, 'financial_survey');
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

        if ($request->user->role === 'ADMIN') {
            $financialSurveys = $financialSurveys
                ->allowedFilters([
                    'name',
                    'is_active',
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

        return ResponseFormatter::collection('financial_surveys', $financialSurveys);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSurveyRequest $request): JsonResponse
    {
        $financialSurveys = new Survey($request->validated());
        $financialSurveys->fill([
            'type' => 'FINANCIAL',
        ]);

        $financialSurveys->save();

        return ResponseFormatter::singleton('financial_survey', $financialSurveys, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Survey $financialSurveys): JsonResponse
    {
        $financialSurveys = QueryBuilder::for(Survey::where('id', $financialSurveys->id)
            ->where('type', 'FINANCIAL')
        )
            ->allowedIncludes([
                AllowedInclude::relationship('questions', 'surveyQuestions'),
            ])
            ->firstOrFail();

        return ResponseFormatter::singleton('financial_survey', $financialSurveys);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSurveyRequest $request, Survey $financialSurveys): JsonResponse
    {
        $financialSurveys->fill($request->validated());
        $financialSurveys->save();

        return ResponseFormatter::singleton('financial_survey', $financialSurveys);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Survey $financialSurveys): JsonResponse
    {
        $financialSurveys->delete();

        return ResponseFormatter::singleton('financial_survey', $financialSurveys);
    }
}
