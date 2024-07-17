<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatMessage\StoreChatMessageRequest;
use App\Http\Requests\ChatMessage\UpdateChatMessageRequest;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class ChatMessageController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ChatMessage::class, 'chat_message');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ChatSession $chatSession): JsonResponse
    {
        $chatMessages = QueryBuilder::for(ChatMessage::class)
            ->allowedIncludes([
                AllowedInclude::relationship('session', 'chatSession'),
            ])
            ->allowedFilters([
                'sender',
            ])
            ->allowedSorts([
                'sender',
                'created_at',
                'updated_at',
            ])
            ->where('session_id', $chatSession->id)
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::collection('chat_messages', $chatMessages);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatMessageRequest $request, ChatSession $chatSession): JsonResponse
    {
        $chatMessage = new ChatMessage($request->validated());
        $chatMessage->fill([
            'session_id' => $chatSession->id,
        ]);
        $chatMessage->save();

        return ResponseFormatter::singleton('chat_message', $chatMessage, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ChatMessage $chatMessage): JsonResponse
    {
        $chatMessage = QueryBuilder::for(ChatMessage::where('id', $chatMessage->id))
            ->allowedIncludes([
                AllowedInclude::relationship('session', 'chatSession'),
            ])
            ->firstOrFail();

        return ResponseFormatter::singleton('chat_message', $chatMessage);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatMessageRequest $request, ChatMessage $chatMessage): JsonResponse
    {
        $chatMessage->fill($request->validated());
        $chatMessage->save();

        return ResponseFormatter::singleton('chat_message', $chatMessage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChatMessage $chatMessage): JsonResponse
    {
        $chatMessage->delete();

        return ResponseFormatter::singleton('chat_message', $chatMessage);
    }
}
