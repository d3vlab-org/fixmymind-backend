<?php
// === app/Services/OpenAIService.php ===
namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAIService
{
    public static function generateReply($text, $context)
    {
        $messages = collect($context)->map(function ($msg) {
            return [
                'role' => $msg['sender'] === 'user' ? 'user' : 'assistant',
                'content' => $msg['message_text']
            ];
        })->toArray();

        $messages[] = ['role' => 'user', 'content' => $text];

        $response = Http::withToken(config('services.openai.key'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => $messages,
            ]);

        return $response->json('choices.0.message.content');
    }
}
