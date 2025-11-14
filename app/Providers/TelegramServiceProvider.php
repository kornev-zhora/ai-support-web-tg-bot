<?php

namespace App\Providers;

use App\Services\TelegramCommandProvider;
use Illuminate\Support\ServiceProvider;

class TelegramServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Auto-bind all Telegram handlers and tag them
        $this->app->bind(\App\Telegram\HelpCommandBotHandler::class);
        $this->app->bind(\App\Telegram\StartCommandBotHandler::class);
        $this->app->bind(\App\Telegram\SettingsCommandBotHandler::class);

        $this->app->tag([
            \App\Telegram\HelpCommandBotHandler::class,
            \App\Telegram\StartCommandBotHandler::class,
            \App\Telegram\SettingsCommandBotHandler::class,
        ], 'telegram.handlers');

        // Single provider receives tagged handlers
        $this->app->singleton(TelegramCommandProvider::class, function ($app) {
            return new TelegramCommandProvider(...$app->tagged('telegram.handlers'));
        });
    }
}
