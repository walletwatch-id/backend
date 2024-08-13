<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hotline\StoreHotlineRequest;
use App\Models\Hotline;
use App\Models\Paylater;
use App\Models\PaylaterHotline;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class PaylaterHotlineController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view,paylater')->only(['index', 'store']);
        $this->middleware('can:create,'.Hotline::class)->only('store');
        $this->middleware('can:view,'.Hotline::class)->only('index');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Paylater $paylater): JsonResponse
    {
        $paylaterHotlines = PaylaterHotline::select('hotline_id')
            ->where('paylater_id', $paylater->id)
            ->get();

        $hotlines = QueryBuilder::for(Hotline::class)
            ->allowedFilters([
                'name',
                'type',
            ])
            ->allowedSorts([
                'name',
                'type',
            ])
            ->whereIn('id', $paylaterHotlines)
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::paginatedCollection('hotlines', $hotlines);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHotlineRequest $request, Paylater $paylater): JsonResponse
    {
        $hotline = new Hotline($request->validated());
        $hotline->save();

        $paylaterHotline = new PaylaterHotline([
            'paylater_id' => $paylater->id,
            'hotline_id' => $hotline->id,
        ]);
        $paylaterHotline->save();

        return ResponseFormatter::singleton('hotline', $hotline, 201);
    }
}
