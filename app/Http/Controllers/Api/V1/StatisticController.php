<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Statistic;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class StatisticController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Statistic::class, 'statistic');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $statistic = QueryBuilder::for(Statistic::class)
            ->allowedIncludes([
                'user',
            ]);

        if ($request->user()->role === 'ADMIN') {
            $statistic = $statistic
                ->allowedFilters([
                    'user_id',
                    'month',
                    'year',
                ])
                ->allowedSorts([
                    'user_id',
                    'month',
                    'year',
                ]);
        } else {
            $statistic = $statistic
                ->allowedFilters([
                    'month',
                    'year',
                ])
                ->allowedSorts([
                    'month',
                    'year',
                ])
                ->where('user_id', $request->user()->id);
        }

        $statistic = $statistic->paginate($request->query('per_page', 10));

        return ResponseFormatter::collection('statistics', $statistic);
    }

    /**
     * Display the specified resource.
     */
    public function show(Statistic $statistic): JsonResponse
    {
        $statistic = QueryBuilder::for(Statistic::where('id', $statistic->id))
            ->allowedIncludes([
                'user',
            ])
            ->firstOrFail();

        return ResponseFormatter::singleton('statistic', $statistic);
    }
}
