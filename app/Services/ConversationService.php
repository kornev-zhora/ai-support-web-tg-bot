<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\Redis;

class ConversationService
{
    public function __construct(
        private readonly GeminiService $geminiService
    ) {}

    /**
     * Find or create a conversation for the given channel and user identifier.
     */
    public function findOrCreateConversation(string $channel, string $userIdentifier, ?array $extraData = null): Conversation
    {
        $conversation = Conversation::query()
            ->where('channel', $channel)
            ->where('user_identifier', $userIdentifier)
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'channel' => $channel,
                'user_identifier' => $userIdentifier,
                'telegram_user_id' => $extraData['telegram_user_id'] ?? null,
                'telegram_username' => $extraData['telegram_username'] ?? null,
                'last_message_at' => now(),
            ]);
        }

        return $conversation;
    }

    /**
     * Store a message and return AI response.
     */
    public function processMessage(Conversation $conversation, string $userMessage): ?string
    {
        // Store user message
        $this->storeMessage($conversation, 'user', $userMessage);

        // Get conversation history
        $history = $this->getConversationHistory($conversation);

        // Generate AI response
        $aiResponse = $this->geminiService->generateResponse($history);

        if ($aiResponse) {
            // Store AI response
            $this->storeMessage($conversation, 'assistant', $aiResponse);
        }

        return $aiResponse;
    }

    /**
     * Store a message in the conversation (Redis cache with 24-hour TTL).
     *
     * @return array{role: string, content: string, created_at: string}
     */
    public function storeMessage(Conversation $conversation, string $role, string $content): array
    {
        $message = [
            'role' => $role,
            'content' => $content,
            'created_at' => now()->toIso8601String(),
        ];

        // Get Redis key for conversation messages
        $key = $this->getMessagesKey($conversation->id);

        // Get existing messages
        $messages = $this->getMessages($conversation->id);

        // Add new message
        $messages[] = $message;

        // Store in Redis with 24-hour TTL (86400 seconds)
        Redis::setex($key, 86400, json_encode($messages));

        $conversation->update([
            'last_message_at' => now(),
        ]);

        return $message;
    }

    /**
     * Get conversation history formatted for AI.
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function getConversationHistory(Conversation $conversation, int $limit = 20): array
    {
        $messages = $this->getMessages($conversation->id);

        // Get last N messages
        $messages = array_slice($messages, -$limit);

        // Return only role and content for AI
        return array_map(fn (array $message) => [
            'role' => $message['role'],
            'content' => $message['content'],
        ], $messages);
    }

    /**
     * Get all messages for a conversation from Redis.
     *
     * @return array<int, array{role: string, content: string, created_at: string}>
     */
    public function getMessages(int $conversationId): array
    {
        $key = $this->getMessagesKey($conversationId);
        $data = Redis::get($key);

        if (! $data) {
            return [];
        }

        return json_decode($data, true) ?? [];
    }

    /**
     * Get Redis key for conversation messages.
     */
    private function getMessagesKey(int $conversationId): string
    {
        return "conversation:{$conversationId}:messages";
    }
}
