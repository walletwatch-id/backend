<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Survey\StoreSurveyRequest;
use App\Http\Requests\Survey\UpdateSurveyRequest;
use App\Models\Survey;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class SurveyController extends Controller
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
        $surveys = QueryBuilder::for(Survey::class)
            ->allowedIncludes([
                'user',
            ]);

        if ($request->user->role === 'ADMIN') {
            $surveys = $surveys
                ->allowedFilters([
                    'user_id',
                ])
                ->allowedSorts([
                    'user_id',
                ]);
        } else {
            $surveys = $surveys
                ->where('user_id', $request->user->id);
        }

        $surveys = $surveys->paginate($request->query('per_page', 10));

        return ResponseFormatter::collection('surveys', $surveys);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSurveyRequest $request): JsonResponse
    {
        $survey = new Survey($request->validated());

        if ($request->user->role !== 'ADMIN') {
            $survey->fill([
                'user_id' => $request->user->id,
            ]);
        }

        $survey->save();

        return ResponseFormatter::singleton('survey', $survey, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Survey $survey): JsonResponse
    {
        $survey = QueryBuilder::for(Survey::where('id', $survey->id))
            ->allowedIncludes([
                'user',
            ])->firstOrFail();

        return ResponseFormatter::singleton('survey', $survey);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSurveyRequest $request, Survey $survey): JsonResponse
    {
        $survey->fill($request->validated());
        $survey->save();

        return ResponseFormatter::singleton('survey', $survey);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Survey $survey): JsonResponse
    {
        $survey->delete();

        return ResponseFormatter::singleton('survey', $survey);
    }
}
