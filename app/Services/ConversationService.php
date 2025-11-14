<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageStat;
use Carbon\Carbon;

class ConversationService
{
    public function __construct(
        private GeminiService $geminiService
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

            $this->incrementConversationCount($channel);
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
     * Store a message in the conversation.
     */
    public function storeMessage(Conversation $conversation, string $role, string $content): Message
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'role' => $role,
            'content' => $content,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        $this->incrementMessageCount($conversation->channel);

        return $message;
    }

    /**
     * Get conversation history formatted for AI.
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function getConversationHistory(Conversation $conversation, int $limit = 20): array
    {
        $messages = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(fn (\App\Models\Message $message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->toArray();

        return $messages;
    }

    /**
     * Increment message count for billing stats.
     */
    private function incrementMessageCount(string $channel): void
    {
        $today = Carbon::today();

        $stat = MessageStat::firstOrCreate(
            [
                'stat_date' => $today,
                'channel' => $channel,
            ],
            [
                'message_count' => 0,
                'conversation_count' => 0,
            ]
        );

        $stat->increment('message_count');
    }

    /**
     * Increment conversation count for billing stats.
     */
    private function incrementConversationCount(string $channel): void
    {
        $today = Carbon::today();

        $stat = MessageStat::firstOrCreate(
            [
                'stat_date' => $today,
                'channel' => $channel,
            ],
            [
                'message_count' => 0,
                'conversation_count' => 0,
            ]
        );

        $stat->increment('conversation_count');
    }
}
