<?php

namespace App\Telegram;

use App\Services\ConversationService;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Stringable;

class SupportBotHandler extends WebhookHandler
{
    public function __construct(
        private readonly ConversationService $conversationService
    )
    {
        parent::__construct();
    }

    /**
     * Handle incoming text messages from Telegram.
     */
    protected function handleChatMessage(Stringable $text): void
    {
        $chatId = $this->chat->chat_id;

        // Find or create conversation
        $conversation = $this->conversationService->findOrCreateConversation(
            channel: 'telegram',
            userIdentifier: (string)$chatId,
            extraData: [
                'telegram_user_id' => $this->message->from()->id(),
                'telegram_username' => $this->message->from()->username(),
            ]
        );

        // Process message and get AI response
        $aiResponse = $this->conversationService->processMessage(
            conversation: $conversation,
            userMessage: (string)$text
        );

        if ($aiResponse) {
            $this->reply($aiResponse);
        } else {
            $this->reply('Sorry, I encountered an error processing your message. Please try again later.');
        }
    }

    /**
     * Handle /start command.
     */
    public function start(): void
    {
        $this->reply('Hello! I am your AI support assistant. How can I help you today?');
    }

    /**
     * Handle /help command.
     */
    public function help(): void
    {
        $this->reply("I'm an AI support bot powered by Gemini. Just send me a message and I'll do my best to help you!");
    }
}
