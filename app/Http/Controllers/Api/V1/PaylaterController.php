<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Paylater\StorePaylaterRequest;
use App\Http\Requests\Paylater\UpdatePaylaterRequest;
use App\Models\Paylater;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class PaylaterController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Paylater::class, 'paylater');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $paylaters = QueryBuilder::for(Paylater::class)
            ->allowedIncludes([
                'hotlines',
            ])
            ->allowedFilters([
                'name',
            ])
            ->allowedSorts([
                'name',
            ])
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::collection('paylaters', $paylaters);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaylaterRequest $request): JsonResponse
    {
        $paylater = new Paylater($request->validated());
        $paylater->save();

        return ResponseFormatter::singleton('paylater', $paylater, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Paylater $paylater): JsonResponse
    {
        $paylater = QueryBuilder::for(Paylater::where('id', $paylater->id))
            ->allowedIncludes([
                'hotlines',
            ])
            ->firstOrFail();

        return ResponseFormatter::singleton('paylater', $paylater);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaylaterRequest $request, Paylater $paylater): JsonResponse
    {
        $paylater->fill($request->validated());
        $paylater->save();

        return ResponseFormatter::singleton('paylater', $paylater);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Paylater $paylater): JsonResponse
    {
        $paylater->delete();

        return ResponseFormatter::singleton('paylater', $paylater);
    }
}
