<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;

    protected string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

    protected string $model = 'gemini-2.0-flash-exp';

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Generate a response from Gemini based on conversation history.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function generateResponse(array $messages): ?string
    {
        try {
            $contents = $this->formatMessagesForGemini($messages);

            $url = $this->apiUrl.$this->model.':generateContent?key='.$this->apiKey;

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 1024,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }

            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Gemini service exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Format messages for Gemini API format.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<int, array{role: string, parts: array<int, array{text: string}>}>
     */
    private function formatMessagesForGemini(array $messages): array
    {
        $contents = [];

        foreach ($messages as $message) {
            $role = $message['role'] === 'assistant' ? 'model' : 'user';

            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $message['content']],
                ],
            ];
        }

        return $contents;
    }
}
