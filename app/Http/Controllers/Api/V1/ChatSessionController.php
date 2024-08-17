<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatSession\StoreChatSessionRequest;
use App\Http\Requests\ChatSession\UpdateChatSessionRequest;
use App\Models\ChatSession;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class ChatSessionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ChatSession::class, 'chat_session');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $chatSessions = QueryBuilder::for(ChatSession::class)
            ->allowedIncludes([
                'user',
                AllowedInclude::relationship('messages', 'chatMessages'),
            ]);

        if ($request->user()->role === 'ADMIN') {
            $chatSessions = $chatSessions
                ->allowedFilters([
                    'user_id',
                    'title',
                ])
                ->allowedSorts([
                    'user_id',
                    'title',
                ]);
        } else {
            $chatSessions = $chatSessions
                ->allowedFilters([
                    'title',
                ])
                ->allowedSorts([
                    'title',
                ])
                ->where('user_id', $request->user()->id);
        }

        $chatSessions = $chatSessions->paginate($request->query('per_page', 10));

        return ResponseFormatter::paginatedCollection('chat_sessions', $chatSessions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatSessionRequest $request): JsonResponse
    {
        $chatSession = new ChatSession($request->validated());

        if ($request->user()->role !== 'ADMIN') {
            $chatSession->fill([
                'user_id' => $request->user()->id,
            ]);
        }

        $chatSession->save();

        return ResponseFormatter::singleton('chat_session', $chatSession, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ChatSession $chatSession): JsonResponse
    {
        $chatSession = QueryBuilder::for(ChatSession::where('id', $chatSession->id))
            ->allowedIncludes([
                'user',
                AllowedInclude::relationship('messages', 'chatMessages'),
            ])
            ->firstOrFail();

        return ResponseFormatter::singleton('chat_session', $chatSession);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatSessionRequest $request, ChatSession $chatSession): JsonResponse
    {
        $chatSession->update($request->validated());

        return ResponseFormatter::singleton('chat_session', $chatSession);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChatSession $chatSession): JsonResponse
    {
        $chatSession->delete();

        return ResponseFormatter::singleton('chat_session', $chatSession);
    }
}
