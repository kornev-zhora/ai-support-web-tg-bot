<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function __construct(
        private readonly ConversationService $conversationService
    ) {}

    /**
     * Display the chat interface.
     */
    public function index(): Response
    {
        return Inertia::render('Chat/Index', [
            'conversations' => [],
        ]);
    }

    /**
     * Send a message and get AI response.
     */
    public function sendMessage(SendMessageRequest $request): JsonResponse
    {
        $conversation = $this->conversationService->findOrCreateConversation(
            channel: 'web',
            userIdentifier: $request->validated('session_id')
        );

        $aiResponse = $this->conversationService->processMessage(
            conversation: $conversation,
            userMessage: $request->validated('message')
        );

        if (! $aiResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get AI response. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $aiResponse,
        ]);
    }

    /**
     * Get conversation history.
     */
    public function history(string $sessionId): JsonResponse
    {
        $conversation = $this->conversationService->findOrCreateConversation(
            channel: 'web',
            userIdentifier: $sessionId
        );

        $messages = $this->conversationService->getMessages($conversation->id);

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }
}
