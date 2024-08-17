<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Jobs\DeleteBlob;
use App\Models\User;
use App\Repositories\StorageFacade;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    public function __construct(
        protected StorageFacade $storageFacade,
    ) {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $users = QueryBuilder::for(User::class)
            ->allowedIncludes([
                'transactions',
                'statistics',
                AllowedInclude::relationship('survey_results', 'surveyResults'),
                AllowedInclude::relationship('chat_sessions', 'chatSessions'),
            ])
            ->allowedFilters([
                'name',
                'email',
                AllowedFilter::exact('role'),
            ])
            ->allowedSorts([
                'id',
                'name',
                'created_at',
                'updated_at',
            ])
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::paginatedCollection('users', $users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $hasPicture = $request->hasFile('picture');

        if ($hasPicture) {
            $manifest = $this->storageFacade->store($request->file('picture'), 'avatar');
        }

        $user = new User(
            $hasPicture
            ? array_replace($request->validated(), ['picture' => $manifest])
            : $request->validated()
        );
        $user->forceFill([
            'password' => $request->password,
            'role' => $request->role ?? 'USER',
        ]);
        $user->save();

        return ResponseFormatter::singleton('user', $user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        $user = QueryBuilder::for(User::where('id', $user->id))
            ->allowedIncludes([
                'transactions',
                'statistics',
                AllowedInclude::relationship('survey_results', 'surveyResults'),
                AllowedInclude::relationship('chat_sessions', 'chatSessions'),
            ])
            ->firstOrFail();

        return ResponseFormatter::singleton('user', $user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $hasPicture = $request->has('picture');
        $hasFilePicture = $request->hasFile('picture');

        if ($hasPicture) {
            $encodedManifest = $user->getRawOriginal('picture');

            dispatch(new DeleteBlob($encodedManifest));

            if ($hasFilePicture) {
                $manifest = $this->storageFacade->store($request->file('picture'), 'avatar');
            } else {
                $manifest = null;
            }
        }

        $user->fill(
            $hasPicture
            ? array_replace($request->validated(), ['picture' => $manifest])
            : $request->validated()
        );

        if ($user->role === 'ADMIN') {
            $user->forceFill([
                'password' => $request->password ?? $user->password,
                'role' => $request->role ?? $user->role,
            ]);
        }

        $user->save();

        return ResponseFormatter::singleton('user', $user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $encodedManifest = $user->getRawOriginal('picture');

        dispatch(new DeleteBlob($encodedManifest));

        $user->delete();

        return ResponseFormatter::singleton('user', $user);
    }
}
