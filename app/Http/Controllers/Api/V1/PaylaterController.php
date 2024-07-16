<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Paylater\StorePaylaterRequest;
use App\Http\Requests\Paylater\UpdatePaylaterRequest;
use App\Jobs\DeleteBlob;
use App\Models\Paylater;
use App\Repositories\StorageFacade;
use App\Utils\Encoder;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class PaylaterController extends Controller
{
    public function __construct(
        protected StorageFacade $storageFacade,
    ) {
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
        if ($request->hasFile('logo')) {
            $manifest = $this->storageFacade->store($request->file('logo'), 'logo/paylaters');
            $encodedManifest = Encoder::base64UrlEncode($manifest);
        }

        $paylater = new Paylater(
            $request->hasFile('logo')
            ? array_replace($request->validated(), ['logo' => $encodedManifest])
            : $request->validated()
        );
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
        if ($request->has('logo')) {
            $strings = explode('/', $paylater->logo);
            $encodedManifest = end($strings);

            if (Encoder::isBase64Url($encodedManifest ?? '')) {
                dispatch(new DeleteBlob($encodedManifest));
            }

            if ($request->hasFile('logo')) {
                $manifest = $this->storageFacade->store($request->file('logo'), 'logo/paylaters');
                $encodedManifest = Encoder::base64UrlEncode($manifest);
            } else {
                $encodedManifest = null;
            }
        }

        $paylater->fill(
            $request->has('logo')
            ? array_replace($request->validated(), ['logo' => $encodedManifest])
            : $request->validated()
        );
        $paylater->save();

        return ResponseFormatter::singleton('paylater', $paylater);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Paylater $paylater): JsonResponse
    {
        $strings = explode('/', $paylater->logo);
        $encodedManifest = end($strings);

        if (Encoder::isBase64Url($encodedManifest ?? '')) {
            dispatch(new DeleteBlob($encodedManifest));
        }

        $paylater->delete();

        return ResponseFormatter::singleton('paylater', $paylater);
    }
}
