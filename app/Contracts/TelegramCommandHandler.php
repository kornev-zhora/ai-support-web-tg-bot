<?php

namespace App\Contracts;

use DefStudio\Telegraph\Handlers\WebhookHandler;

interface TelegramCommandHandler
{
    public function supportCommand(string $command): bool;

    public function run(WebhookHandler $handler, string $parameter = ''): void;
}
