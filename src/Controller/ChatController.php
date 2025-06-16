<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use OpenApi\Annotations as OA;

class ChatController
{
    private $client;
    private $apiKey;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? '';
    }
/**
 * @OA\Post(
 *     path="/api/chat",
 *     summary="Wysyła wiadomość terapeutyczną do modelu GPT i zwraca odpowiedź",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"message"},
 *             @OA\Property(property="message", type="string", example="Czuję się samotny i zniechęcony.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Odpowiedź od AI",
 *         @OA\JsonContent(
 *             @OA\Property(property="response", type="string", example="Rozumiem, że czujesz się samotny. Czy chcesz o tym porozmawiać?")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Brak wiadomości w żądaniu"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Błąd podczas komunikacji z OpenAI"
 *     )
 * )
 */
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