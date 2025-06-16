<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatController
{
    private $client;
    private $apiKey;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? '';
    }

    #[Route('/api/chat', name: 'chat', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';

        if (!$message) {
            return new JsonResponse(['error' => 'Brak wiadomości'], 400);
        }

        $response = $this->client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'Jesteś wspierającym terapeutą. Odpowiadasz po polsku.'],
                    ['role' => 'user', 'content' => $message],
                ],
            ],
        ]);

        $data = $response->toArray();
        $reply = $data['choices'][0]['message']['content'] ?? 'Brak odpowiedzi.';

        return new JsonResponse(['response' => $reply]);
    }
}