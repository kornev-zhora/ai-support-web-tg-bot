<?php

namespace App\Telegram;

use App\Contracts\TelegramCommandHandler;
use App\Enums\TelegramCommand;
use App\Services\ConversationService;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use Illuminate\Support\Stringable;

class SettingsCommandBotHandler extends WebhookHandler implements TelegramCommandHandler
{
    public function __construct(
        private readonly ConversationService $conversationService
    ) {
        parent::__construct();
    }

    public function supportCommand(string $command): bool
    {
        return $command === TelegramCommand::SETTINGS->value;
    }

    public function run(WebhookHandler $handler, string $parameter = ''): void
    {
        $this->setHandler($handler);
        $this->settings();
    }

    private function setHandler(WebhookHandler $handler): void
    {
        $this->chat = $handler->chat;
        $this->message = $handler->message;
        $this->callbackQuery = $handler->callbackQuery;
    }

    /**
     * Handle /settings command.
     */
    public function settings(): void
    {
        // Inline keyboard with action buttons (works with multiple bots)
        $inlineKeyboard = Keyboard::make()->buttons([
            Button::make('🔗 Visit Website')->url('https://example.com'),
            Button::make('📞 Contact Support')->action('contact_support'),
            Button::make('📊 View Stats')->action('view_stats'),
            Button::make('🗑️ Clear History')->action('clear_history')->param('confirm', 'yes'),
        ])->chunk(2); // 2 buttons per row

        // Send message with inline keyboard using chat instance (supports multiple bots)
        $this->chat->message('⚙️ <b>Bot Settings</b>\n\nChoose an option:')
            ->keyboard($inlineKeyboard)
            ->send();

        // Reply keyboard for common settings (shown at bottom of chat)
        $replyKeyboard = ReplyKeyboard::make()->buttons([
            ReplyButton::make('👤 Gender'),
            ReplyButton::make('🌍 Language'),
            ReplyButton::make('📋 Support Topic'),
            ReplyButton::make('📍 Location'),
            ReplyButton::make('❌ Close Settings'),
        ])->chunk(2); // 2 buttons per row

        // Send reply keyboard using chat instance (same pattern as inline keyboard)
        $this->chat->message('Or use quick settings below:')
            ->replyKeyboard($replyKeyboard)
            ->send();
    }

    /**
     * Handle inline button callback for "Contact Support"
     */
    protected function contact_support(): void
    {
        $this->chat->message('📞 <b>Contact Support</b>\n\nYou can reach us at:\n📧 support@example.com\n📱 +1 234 567 8900')
            ->send();
    }

    /**
     * Handle inline button callback for "View Stats"
     */
    protected function view_stats(): void
    {
        $chatId = $this->chat->chat_id;
        $conversation = $this->conversationService->findOrCreateConversation(
            channel: 'telegram',
            userIdentifier: (string) $chatId
        );

        $messages = $this->conversationService->getMessages($conversation->id);
        $messageCount = count($messages);

        $this->chat->message("📊 <b>Your Statistics</b>\n\n💬 Total messages: {$messageCount}\n🤖 Bot: Active\n📅 Member since: {$conversation->created_at->format('M d, Y')}")
            ->send();
    }

    /**
     * Handle inline button callback for "Clear History"
     */
    protected function clear_history(): void
    {
        $chatId = $this->chat->chat_id;
        $conversation = $this->conversationService->findOrCreateConversation(
            channel: 'telegram',
            userIdentifier: (string) $chatId
        );

        // Clear messages from Redis
        \Illuminate\Support\Facades\Redis::del("conversation:{$conversation->id}:messages");

        $this->chat->message('🗑️ <b>History Cleared</b>\n\nYour conversation history has been deleted.')
            ->send();
    }

    /**
     * Handle text messages for settings.
     */
    protected function handleChatMessage(Stringable $text): void
    {
        $message = (string) $text;

        match ($message) {
            '👤 Gender' => $this->showGenderSettings(),
            '🌍 Language' => $this->showLanguageSettings(),
            '📋 Support Topic' => $this->showTopicSettings(),
            '📍 Location' => $this->showLocationSettings(),
            '❌ Close Settings' => $this->closeSettings(),
            default => $this->handleSettingSelection($message)
        };
    }

    private function showGenderSettings(): void
    {
        $keyboard = ReplyKeyboard::make()->buttons([
            ReplyButton::make('👨 Male'),
            ReplyButton::make('👩 Female'),
            ReplyButton::make('⚧️ Other'),
            ReplyButton::make('🔙 Back to Settings'),
        ]);

        $this->chat->message('👤 Select your gender:')
            ->replyKeyboard($keyboard)
            ->send();
    }

    private function showLanguageSettings(): void
    {
        $keyboard = ReplyKeyboard::make()->buttons([
            ReplyButton::make('🇺🇸 English'),
            ReplyButton::make('🇪🇸 Spanish'),
            ReplyButton::make('🇫🇷 French'),
            ReplyButton::make('🇩🇪 German'),
            ReplyButton::make('🔙 Back to Settings'),
        ]);

        $this->chat->message('🌍 Select your language:')
            ->replyKeyboard($keyboard)
            ->send();
    }

    private function showTopicSettings(): void
    {
        $keyboard = ReplyKeyboard::make()->buttons([
            ReplyButton::make('💻 Technical Support'),
            ReplyButton::make('💰 Billing'),
            ReplyButton::make('📦 Product Info'),
            ReplyButton::make('❓ General Help'),
            ReplyButton::make('🔙 Back to Settings'),
        ]);

        $this->chat->message('📋 Select your preferred support topic:')
            ->replyKeyboard($keyboard)
            ->send();
    }

    private function showLocationSettings(): void
    {
        $keyboard = ReplyKeyboard::make()->buttons([
            ReplyButton::make('🇺🇸 United States'),
            ReplyButton::make('🇬🇧 United Kingdom'),
            ReplyButton::make('🇨🇦 Canada'),
            ReplyButton::make('🇦🇺 Australia'),
            ReplyButton::make('🌍 Other'),
            ReplyButton::make('🔙 Back to Settings'),
        ]);

        $this->chat->message('📍 Select your location:')
            ->replyKeyboard($keyboard)
            ->send();
    }

    private function handleSettingSelection(string $message): void
    {
        $setting = null;
        $value = null;

        // Gender settings
        if (str_contains($message, 'Male') || str_contains($message, 'Female') || str_contains($message, 'Other')) {
            $setting = 'gender';
            $value = match (true) {
                str_contains($message, 'Male') => 'Male',
                str_contains($message, 'Female') => 'Female',
                str_contains($message, 'Other') => 'Other',
                default => null
            };
        } // Language settings
        elseif (str_contains($message, 'English') || str_contains($message, 'Spanish') || str_contains($message, 'French') || str_contains($message, 'German')) {
            $setting = 'language';
            $value = match (true) {
                str_contains($message, 'English') => 'English',
                str_contains($message, 'Spanish') => 'Spanish',
                str_contains($message, 'French') => 'French',
                str_contains($message, 'German') => 'German',
                default => null
            };
        } // Topic settings
        elseif (str_contains($message, 'Technical') || str_contains($message, 'Billing') || str_contains($message, 'Product') || str_contains($message, 'General')) {
            $setting = 'support_topic';
            $value = match (true) {
                str_contains($message, 'Technical') => 'Technical Support',
                str_contains($message, 'Billing') => 'Billing',
                str_contains($message, 'Product') => 'Product Info',
                str_contains($message, 'General') => 'General Help',
                default => null
            };
        } // Location settings
        elseif (str_contains($message, 'United States') || str_contains($message, 'United Kingdom') || str_contains($message, 'Canada') || str_contains($message, 'Australia') || $message === '🌍 Other') {
            $setting = 'location';
            $value = match (true) {
                str_contains($message, 'United States') => 'United States',
                str_contains($message, 'United Kingdom') => 'United Kingdom',
                str_contains($message, 'Canada') => 'Canada',
                str_contains($message, 'Australia') => 'Australia',
                $message === '🌍 Other' => 'Other',
                default => null
            };
        } // Back to settings
        elseif (str_contains($message, 'Back to Settings')) {
            $this->settings();

            return;
        }

        if ($setting && $value) {
            $this->saveUserSetting($setting, $value);
            $this->chat->message("✅ {$setting} set to: {$value}")->send();
        }
    }

    private function closeSettings(): void
    {
        $this->chat->message('⚙️ Settings closed.')
            ->removeReplyKeyboard()
            ->send();
    }

    private function saveUserSetting(string $key, string $value): void
    {
        $chatId = $this->chat->chat_id;

        $conversation = $this->conversationService->findOrCreateConversation(
            channel: 'telegram',
            userIdentifier: (string) $chatId,
            extraData: [
                'telegram_user_id' => $this->message->from()->id(),
                'telegram_username' => $this->message->from()->username(),
            ]
        );

        // Ensure we always work with a plain associative array.
        // conversation->extra_data may be null, string, object or array at runtime.
        $rawExtra = $conversation->extra_data ?? [];

        // If it's an object (or other non-array), cast to array. If it's already an array, keep it.
        if (! is_array($rawExtra)) {
            // (array) is safe: object -> array, null -> [], string -> ['0' => '...'] but we prefer empty array
            // so prefer to reset on non-array values:
            $extraData = [];
        } else {
            $extraData = $rawExtra;
        }

        /** @var array<string, mixed> $extraData */ // <- tells PHPStan this is a general associative array
        // Read existing settings (if any)
        $settings = $extraData['settings'] ?? [];
        if (! is_array($settings)) {
            $settings = [];
        }

        $settings[$key] = $value;

        // Persist merged extra_data
        $conversation->update([
            'extra_data' => array_merge($extraData, ['settings' => $settings]),
        ]);
    }
}
