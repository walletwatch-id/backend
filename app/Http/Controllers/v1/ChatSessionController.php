<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatSession\StoreChatSessionRequest;
use App\Http\Requests\ChatSession\UpdateChatSessionRequest;
use App\Models\ChatSession;

class ChatSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatSessionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ChatSession $chatSession)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatSessionRequest $request, ChatSession $chatSession)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChatSession $chatSession)
    {
        //
    }
}
