<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hotline\StoreHotlineRequest;
use App\Models\Hotline;
use App\Models\Instance;
use App\Models\InstanceHotline;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class InstanceHotlineController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view,instance')->only(['index', 'store']);
        $this->middleware('can:create,'.Hotline::class)->only('store');
        $this->middleware('can:view,'.Hotline::class)->only('index');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Instance $instance): JsonResponse
    {
        $instanceHotlines = InstanceHotline::select('hotline_id')
            ->where('instance_id', $instance->id)
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
            ->whereIn('id', $instanceHotlines)
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::collection('hotlines', $hotlines);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHotlineRequest $request, Instance $instance): JsonResponse
    {
        $hotline = new Hotline($request->validated());
        $hotline->save();

        $instanceHotline = new InstanceHotline([
            'instance_id' => $instance->id,
            'hotline_id' => $hotline->id,
        ]);
        $instanceHotline->save();

        return ResponseFormatter::singleton('hotline', $hotline, 201);
    }
}
