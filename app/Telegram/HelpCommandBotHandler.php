<?php

namespace App\Telegram;

use App\Contracts\TelegramCommandHandler;
use App\Enums\TelegramCommand;
use DefStudio\Telegraph\Handlers\WebhookHandler;

class HelpCommandBotHandler extends WebhookHandler implements TelegramCommandHandler
{
    public function supportCommand(string $command): bool
    {
        return $command === TelegramCommand::HELP->value;
    }

    public function run(\DefStudio\Telegraph\Handlers\WebhookHandler $handler, string $parameter = ''): void
    {
        $handler->reply("I'm an AI support bot powered by Gemini. Just send me a message and I'll do my best to help you!");
    }
}
