<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hotline\UpdateHotlineRequest;
use App\Models\Hotline;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\QueryBuilder;

class HotlineController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Hotline::class, 'hotline');
    }

    /**
     * Display the specified resource.
     */
    public function show(Hotline $hotline): JsonResponse
    {
        $hotline = QueryBuilder::for(Hotline::where('id', $hotline->id))
            ->firstOrFail();

        return ResponseFormatter::singleton('hotline', $hotline);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHotlineRequest $request, Hotline $hotline): JsonResponse
    {
        $hotline->update($request->validated());

        return ResponseFormatter::singleton('hotline', $hotline);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hotline $hotline): JsonResponse
    {
        $hotline->delete();

        return ResponseFormatter::singleton('hotline', $hotline);
    }
}
