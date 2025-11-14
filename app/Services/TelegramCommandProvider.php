<?php

namespace App\Services;

use App\Contracts\TelegramCommandHandler;

class TelegramCommandProvider
{
    private array $handlers = [];

    public function __construct(TelegramCommandHandler ...$handlers)
    {
        $this->handlers = $handlers;
    }

    public function provide(string $command): ?TelegramCommandHandler
    {
        foreach ($this->handlers as $handler) {
            /** @var TelegramCommandHandler $handler */
            if ($handler->supportCommand($command)) {
                return $handler;
            }
        }

        return null;
    }
}
