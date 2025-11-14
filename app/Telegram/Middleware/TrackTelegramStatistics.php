<?php

namespace App\Telegram\Middleware;

use App\Models\Conversation;
use App\Models\MessageStat;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackTelegramStatistics
{
    /**
     * Handle incoming Telegram webhook request and track statistics.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Track statistics after webhook is processed successfully
        if ($response->isSuccessful()) {
            $this->trackStatistics($request);
        }

        return $response;
    }

    /**
     * Track Telegram chat statistics.
     */
    private function trackStatistics(Request $request): void
    {
        $channel = 'telegram';

        // Get chat_id from webhook payload
        $payload = $request->all();
        $chatId = $payload['message']['chat']['id']
            ?? $payload['callback_query']['message']['chat']['id']
            ?? null;

        if (! $chatId) {
            return;
        }

        $today = Carbon::today();

        // Check if this is a new conversation today
        $conversation = Conversation::query()
            ->where('channel', $channel)
            ->where('user_identifier', (string) $chatId)
            ->whereDate('created_at', $today)
            ->first();

        $isNewConversation = $conversation && $conversation->created_at->isToday();

        // Get or create stats for today
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

        // Increment message count (always)
        $stat->increment('message_count');

        // Increment conversation count only for new conversations
        if ($isNewConversation) {
            $stat->increment('conversation_count');
        }
    }
}
