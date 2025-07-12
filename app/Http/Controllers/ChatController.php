<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * @OA\Info(
 *     title="FixMyMind API",
 *     version="1.0.0",
 *     description="API for therapeutic voice and text chat with AI",
 *     @OA\Contact(
 *         email="contact@fixmymind.org"
 *     )
 * )
 * @OA\Server(
 *     url="https://api.fixmymind.org/api",
 *     description="Production API Server"
 * )
 * @OA\Server(
 *     url="/api",
 *     description="Local API Server"
 * )
 */
class ChatController extends Controller
{
    /**
     * @OA\Post(
     *     path="/chat",
     *     summary="Send a text message to the AI therapist",
     *     tags={"Text Chat"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="messages",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="role", type="string", enum={"user", "assistant"}),
     *                     @OA\Property(property="content", type="string")
     *                 ),
     *                 description="Array of conversation messages"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="AI response",
     *         @OA\JsonContent(
     *             @OA\Property(property="reply", type="string", description="AI therapist response")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function send(Request $request)
    {
        $messages = $request->input('messages', []);
        $model = 'gpt-4o'; // lub gpt-3.5-turbo

        $response = Http::withToken(env('OPENAI_API_KEY'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => array_merge([
                    ['role' => 'system', 'content' => 'You are a compassionate Polish-speaking AI therapist. Answer with empathy and curiosity.'],
                ], $messages),
                'temperature' => 0.7,
            ]);

        if ($response->failed()) {
            return response()->json(['error' => 'AI request failed'], 500);
        }

        return response()->json([
            'reply' => $response->json()['choices'][0]['message']['content'] ?? 'Nie mogę teraz odpowiedzieć.',
        ]);
    }
}
