<?php

namespace App\Telegram;

use App\Contracts\TelegramCommandHandler;
use App\Enums\TelegramCommand;
use DefStudio\Telegraph\Handlers\WebhookHandler;

class StartCommandBotHandler extends WebhookHandler implements TelegramCommandHandler
{
    public function supportCommand(string $command): bool
    {
        return $command === TelegramCommand::START->value;
    }

    public function run(WebhookHandler $handler, string $parameter = ''): void
    {
        $handler->reply('Hello! I am your AI support assistant. How can I help you today?');
    }
}
