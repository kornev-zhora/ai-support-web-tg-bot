<?php

namespace App\Telegram;

use App\Services\ConversationService;
use App\Services\TelegramCommandProvider;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Stringable;

class SupportBotHandler extends WebhookHandler
{
    public function __construct(
        private readonly ConversationService $conversationService,
        private readonly TelegramCommandProvider $commandProvider
    ) {
        parent::__construct();
    }

    /**
     * Handle incoming text messages from Telegram.
     */
    protected function handleChatMessage(Stringable $text): void
    {
        $message = (string) $text;

        // Handle settings menu interactions
        if ($this->isSettingsMessage($message)) {
            $this->handleSettingsMessage($message);

            return;
        }

        $chatId = $this->chat->chat_id;

        // Find or create conversation
        $conversation = $this->conversationService->findOrCreateConversation(
            channel: 'telegram',
            userIdentifier: (string) $chatId,
            extraData: [
                'telegram_user_id' => $this->message->from()->id(),
                'telegram_username' => $this->message->from()->username(),
            ]
        );

        // Process message and get AI response
        $aiResponse = $this->conversationService->processMessage(
            conversation: $conversation,
            userMessage: (string) $text
        );

        if ($aiResponse) {
            $this->reply($aiResponse);

            return;
        }

        $this->reply('Sorry, I encountered an error processing your message. Please try again later.');
    }

    private function isSettingsMessage(string $message): bool
    {
        return str_contains($message, 'Gender') || str_contains($message, 'Language') ||
               str_contains($message, 'Support Topic') || str_contains($message, 'Location') ||
               str_contains($message, 'Close Settings');
    }

    private function handleSettingsMessage(string $message): void
    {
        if (str_contains($message, 'Close Settings')) {
            $this->chat
                ->message('⚙️ Settings closed.')
                ->removeReplyKeyboard()
                ->send();

            return;
        }
        $this->reply('Settings menu selected: '.$message);

    }

    protected function handleCommand(Stringable $text): void
    {
        [$command, $parameter] = $this->parseCommand($text);

        $commandHandler = $this->commandProvider->provide($command);

        if ($commandHandler === null) {
            $this->handleUnknownCommand($text);

            return;
        }

        $commandHandler->run($this, $parameter);
    }
}
