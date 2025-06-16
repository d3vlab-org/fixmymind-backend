<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class HealthController
{
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
    #[Route('/health', name: 'health_check', methods: ['GET'])]
    public function check(): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']);
    }
}