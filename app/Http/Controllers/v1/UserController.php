<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Jobs\DeleteBlob;
use App\Models\User;
use App\Utils\Encoder;
use App\Utils\ResponseFormatter;
use App\Utils\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;
use Str;

class UserController extends Controller
{
    public function __construct()
    {
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
                'surveys',
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

        return ResponseFormatter::collection('users', $users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        if ($request->hasFile('picture')) {
            $id = Storage::store($request->file('picture'), 'avatar');
            $encoded_id = Encoder::base64UrlEncode($id);
        }

        $user = new User(
            $request->hasFile('picture')
            ? array_replace($request->validated(), ['picture' => $encoded_id])
            : $request->validated()
        );
        $user->password = $request->password ?? Str::random(16);
        $user->role = $request->role ?? 'USER';
        $user->save();

        return ResponseFormatter::singleton('user', $user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request): JsonResponse
    {
        $user = QueryBuilder::for(User::where('id', $request->user))
            ->allowedIncludes([
                'transactions',
                'surveys',
                AllowedInclude::relationship('chat_sessions', 'chatSessions'),
            ])->firstOrFail();

        return ResponseFormatter::singleton('user', $user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        if ($request->has('picture')) {
            $strings = explode('/', $user->picture);
            $id = end($strings);

            if (Encoder::isBase64Url($id ?? '')) {
                DeleteBlob::dispatch($id);
            }

            if ($request->hasFile('picture')) {
                $id = Storage::store($request->file('picture'), 'avatar');
                $encoded_id = Encoder::base64UrlEncode($id);
            } else {
                $encoded_id = null;
            }
        }

        $user->fill(
            $request->has('picture')
            ? array_replace($request->validated(), ['picture' => $encoded_id])
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
        $strings = explode('/', $user->picture);
        $id = end($strings);

        if (Encoder::isBase64Url($id ?? '')) {
            DeleteBlob::dispatch($id);
        }

        $user->delete();

        return ResponseFormatter::singleton('user', $user);
    }
}
