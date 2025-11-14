<?php

namespace App\Http\Middleware;

use App\Models\Conversation;
use App\Models\MessageStat;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackChatStatistics
{
    /**
     * Handle an incoming request and track chat statistics.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track successful chat message requests
        if ($response->isSuccessful() && $request->routeIs('chat.send')) {
            $this->trackStatistics($request);
        }

        return $response;
    }

    /**
     * Track chat statistics after successful message.
     */
    private function trackStatistics(Request $request): void
    {
        $channel = 'web';
        $sessionId = $request->input('session_id');

        if (! $sessionId) {
            return;
        }

        $today = Carbon::today();

        // Check if this is a new conversation today
        $conversation = Conversation::query()
            ->where('channel', $channel)
            ->where('user_identifier', $sessionId)
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
