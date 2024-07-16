<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instance\StoreInstanceRequest;
use App\Http\Requests\Instance\UpdateInstanceRequest;
use App\Jobs\DeleteBlob;
use App\Models\Instance;
use App\Repositories\StorageFacade;
use App\Utils\Encoder;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class InstanceController extends Controller
{
    public function __construct(
        protected StorageFacade $storageFacade,
    ) {
        $this->authorizeResource(Instance::class, 'instance');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $instances = QueryBuilder::for(Instance::class)
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

        return ResponseFormatter::collection('instances', $instances);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInstanceRequest $request): JsonResponse
    {
        if ($request->hasFile('logo')) {
            $manifest = $this->storageFacade->store($request->file('logo'), 'logo/instances');
            $encodedManifest = Encoder::base64UrlEncode($manifest);
        }

        $instance = new Instance(
            $request->hasFile('logo')
            ? array_replace($request->validated(), ['logo' => $encodedManifest])
            : $request->validated()
        );
        $instance->save();

        return ResponseFormatter::singleton('instance', $instance, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Instance $instance): JsonResponse
    {
        $instance = QueryBuilder::for(Instance::where('id', $instance->id))
            ->allowedIncludes([
                'hotlines',
            ])
            ->firstOrFail();

        return ResponseFormatter::singleton('instance', $instance);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInstanceRequest $request, Instance $instance): JsonResponse
    {
        if ($request->has('logo')) {
            $strings = explode('/', $instance->logo);
            $encodedManifest = end($strings);

            if (Encoder::isBase64Url($encodedManifest ?? '')) {
                DeleteBlob::dispatch($encodedManifest);
            }

            if ($request->hasFile('logo')) {
                $manifest = $this->storageFacade->store($request->file('logo'), 'logo/instances');
                $encodedManifest = Encoder::base64UrlEncode($manifest);
            } else {
                $encodedManifest = null;
            }
        }

        $instance->fill(
            $request->has('logo')
            ? array_replace($request->validated(), ['logo' => $encodedManifest])
            : $request->validated()
        );
        $instance->save();

        return ResponseFormatter::singleton('instance', $instance);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Instance $instance): JsonResponse
    {
        $strings = explode('/', $instance->logo);
        $encodedManifest = end($strings);

        if (Encoder::isBase64Url($encodedManifest ?? '')) {
            dispatch(new DeleteBlob($encodedManifest));
        }

        $instance->delete();

        return ResponseFormatter::singleton('instance', $instance);
    }
}
