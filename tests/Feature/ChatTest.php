<?php

use App\Models\Conversation;
use App\Models\MessageStat;
use App\Services\ConversationService;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

// Clear Redis before each test
beforeEach(function () {
    Redis::flushdb();
});

test('chat page can be rendered', function () {
    $response = $this->get('/chat');

    $response->assertSuccessful();
});

test('can send a message and receive ai response', function () {
    // Mock Gemini service
    $geminiMock = mock(GeminiService::class);
    $geminiMock->shouldReceive('generateResponse')
        ->once()
        ->andReturn('This is a test AI response');

    $sessionId = 'test_session_123';

    $response = $this->postJson('/api/chat/send', [
        'message' => 'Hello, AI!',
        'session_id' => $sessionId,
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'message' => 'This is a test AI response',
    ]);

    // Verify conversation was created
    $this->assertDatabaseHas('conversations', [
        'channel' => 'web',
        'user_identifier' => $sessionId,
    ]);

    // Verify messages were stored in Redis
    $conversation = Conversation::query()->where('user_identifier', $sessionId)->first();
    $conversationService = app(ConversationService::class);
    $messages = $conversationService->getMessages($conversation->id);

    expect($messages)->toHaveCount(2);
    expect($messages[0]['role'])->toBe('user');
    expect($messages[0]['content'])->toBe('Hello, AI!');
    expect($messages[1]['role'])->toBe('assistant');
    expect($messages[1]['content'])->toBe('This is a test AI response');

    // Verify stats were updated
    $this->assertDatabaseHas('message_stats', [
        'channel' => 'web',
        'stat_date' => today(),
    ]);
});

test('message validation fails with missing message', function () {
    $response = $this->postJson('/api/chat/send', [
        'session_id' => 'test_session',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['message']);
});

test('message validation fails with missing session id', function () {
    $response = $this->postJson('/api/chat/send', [
        'message' => 'Hello',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['session_id']);
});

test('message validation fails with message too long', function () {
    $response = $this->postJson('/api/chat/send', [
        'message' => str_repeat('a', 2001),
        'session_id' => 'test_session',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['message']);
});

test('can retrieve conversation history', function () {
    $sessionId = 'test_session_456';

    $conversation = Conversation::create([
        'channel' => 'web',
        'user_identifier' => $sessionId,
        'last_message_at' => now(),
    ]);

    // Store messages in Redis
    $conversationService = app(ConversationService::class);
    $conversationService->storeMessage($conversation, 'user', 'First message');
    $conversationService->storeMessage($conversation, 'assistant', 'First response');

    $response = $this->getJson("/api/chat/history/{$sessionId}");

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
    ]);

    $messages = $response->json('messages');
    expect($messages)->toHaveCount(2);
    expect($messages[0]['role'])->toBe('user');
    expect($messages[0]['content'])->toBe('First message');
    expect($messages[1]['role'])->toBe('assistant');
    expect($messages[1]['content'])->toBe('First response');
});

test('conversation history returns empty for new session', function () {
    $sessionId = 'new_session_789';

    $response = $this->getJson("/api/chat/history/{$sessionId}");

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'messages' => [],
    ]);
});

test('message stats are incremented correctly', function () {
    $geminiMock = mock(GeminiService::class);
    $geminiMock->shouldReceive('generateResponse')
        ->once()
        ->andReturn('Response');

    $this->postJson('/api/chat/send', [
        'message' => 'Test',
        'session_id' => 'test_session',
    ]);

    $stat = MessageStat::query()
        ->where('channel', 'web')
        ->where('stat_date', today())
        ->first();

    expect($stat)->not->toBeNull();
    expect($stat->message_count)->toBeGreaterThan(0);
    expect($stat->conversation_count)->toBeGreaterThan(0);
});
