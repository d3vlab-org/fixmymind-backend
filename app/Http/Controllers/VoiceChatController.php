<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\VoiceSession;

/**
 * @OA\Tag(
 *     name="Voice Chat",
 *     description="Voice chat endpoints for audio processing and AI responses"
 * )
 * @OA\Tag(
 *     name="Voice Sessions",
 *     description="Voice session management endpoints"
 * )
 */

class VoiceChatController extends Controller
{
    /**
     * @OA\Post(
     *     path="/voice-chat",
     *     summary="Handle voice chat upload and response",
     *     tags={"Voice Chat"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Audio file to upload (supported formats: wav, mp3, mp4, m4a, flac, webm, ogg)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="transcript", type="string", description="Transcribed text from audio"),
     *             @OA\Property(property="text", type="string", description="AI response text"),
     *             @OA\Property(property="audio_url", type="string", format="url", description="URL to generated audio response")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Error message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Error message")
     *         )
     *     )
     * )
     */
    public function handle(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded.'], 400);
        }

        $file = $request->file('file');

        // Validate file format
        $allowedExtensions = ['wav', 'mp3', 'mp4', 'm4a', 'flac', 'webm', 'ogg'];
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, $allowedExtensions)) {
            return response()->json([
                'error' => 'Invalid audio file format. Supported formats: ' . implode(', ', $allowedExtensions)
            ], 400);
        }

        $filename = Str::uuid() . '.' . $ext;
        $tmpPath = storage_path('app/tmp');

        if (!file_exists($tmpPath)) {
            mkdir($tmpPath, 0755, true);
        }

        $file->move($tmpPath, $filename);
        $fullPath = $tmpPath . '/' . $filename;

        try {
            // 1. Transcription with Whisper
            $whisper = Http::withToken(env('OPENAI_API_KEY'))
                ->attach('file', file_get_contents($fullPath), $filename)
                ->post('https://api.openai.com/v1/audio/transcriptions', [
                    'model' => 'whisper-1',
                    'language' => 'pl',
                ]);

            if ($whisper->failed()) {
                return response()->json(['error' => 'Transcription failed.'], 500);
            }

            $transcript = $whisper->json('text');

            // 2. GPT response
            $chat = Http::withToken(env('OPENAI_API_KEY'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Jesteś pomocnym terapeutą. Odpowiadaj po polsku, ciepło i krótko.'],
                        ['role' => 'user', 'content' => $transcript],
                    ],
                    'max_tokens' => 150,
                    'temperature' => 0.7,
                ]);

            if ($chat->failed()) {
                return response()->json(['error' => 'AI response failed.'], 500);
            }

            $reply = $chat->json('choices.0.message.content');

            // 3. Text-to-speech with ElevenLabs
            $audioUrl = null;
            $warning = null;

            if (env('ELEVENLABS_API_KEY') && env('ELEVENLABS_VOICE_ID')) {
                $tts = Http::withHeaders([
                    'xi-api-key' => env('ELEVENLABS_API_KEY'),
                    'Accept' => 'audio/mpeg',
                    'Content-Type' => 'application/json',
                ])->post("https://api.elevenlabs.io/v1/text-to-speech/" . env('ELEVENLABS_VOICE_ID'), [
                    'text' => $reply,
                    'model_id' => 'eleven_multilingual_v2',
                    'voice_settings' => [
                        'stability' => 0.7,
                        'similarity_boost' => 0.9,
                    ],
                ]);

                if ($tts->successful()) {
                    $audioFilename = 'ai/' . Str::uuid() . '.mp3';
                    Storage::disk('public')->put($audioFilename, $tts->body());
                    $audioUrl = asset('storage/' . $audioFilename);
                } else {
                    $warning = 'Voice generation temporarily unavailable. Text response provided instead.';
                }
            } else {
                $warning = 'Voice generation not configured. Text response provided instead.';
            }

            return response()->json([
                'transcript' => $transcript,
                'text' => $reply,
                'audio_url' => $audioUrl,
                'warning' => $warning
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Processing failed: ' . $e->getMessage()], 500);
        } finally {
            // Clean up temporary file
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/voice-sessions",
     *     summary="Get all voice chat sessions",
     *     tags={"Voice Sessions"},
     *     @OA\Response(
     *         response=200,
     *         description="List of voice chat sessions",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="user_id", type="integer", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function getSessions(Request $request)
    {
        // TODO: w przyszłości podmień null na $request->user()->id
        $sessions = VoiceSession::orderBy('created_at', 'desc')->get();

        return response()->json($sessions);
    }

    /**
     * @OA\Post(
     *     path="/voice-sessions",
     *     summary="Create a new voice chat session",
     *     tags={"Voice Sessions"},
     *     @OA\Response(
     *         response=200,
     *         description="Session created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="Session ID")
     *         )
     *     )
     * )
     */
    public function createSession()
    {
        $session = VoiceSession::create([
            'user_id' => null, // lub auth()->id()
        ]);

        return response()->json(['id' => $session->id]);
    }

    /**
     * @OA\Post(
     *     path="/voice-sessions/{id}/messages",
     *     summary="Store a new message in a voice session",
     *     tags={"Voice Sessions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Audio file to upload"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message stored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="transcript", type="string"),
     *             @OA\Property(property="text", type="string"),
     *             @OA\Property(property="audio_url", type="string"),
     *             @OA\Property(property="messages", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function storeMessage(Request $request, $sessionId)
    {
        if (!is_numeric($sessionId)) {
            return response()->json(['error' => 'Invalid session ID'], 400);
        }

        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded.'], 400);
        }

        $session = VoiceSession::findOrFail($sessionId);
        $file = $request->file('file');

        $allowedExtensions = ['wav', 'mp3', 'mp4', 'm4a', 'flac', 'webm', 'ogg'];
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, $allowedExtensions)) {
            return response()->json(['error' => 'Unsupported audio format'], 400);
        }

        $filename = Str::uuid() . '.' . $ext;
        $tmpPath = storage_path('app/tmp');

        if (!file_exists($tmpPath)) {
            mkdir($tmpPath, 0755, true);
        }

        $file->move($tmpPath, $filename);
        $fullPath = $tmpPath . '/' . $filename;

        // 1. Transkrypcja
        $whisper = Http::withToken(env('OPENAI_API_KEY'))
            ->attach('file', file_get_contents($fullPath), $filename)
            ->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1',
                'language' => 'pl',
            ]);

        if ($whisper->failed()) {
            return response()->json(['error' => 'Whisper failed.'], 500);
        }

        $transcript = $whisper->json('text');

        // 2. GPT
        $chat = Http::withToken(env('OPENAI_API_KEY'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'Jesteś pomocnym terapeutą. Odpowiadaj po polsku, ciepło i krótko.'],
                    ['role' => 'user', 'content' => $transcript],
                ],
                'max_tokens' => 150,
                'temperature' => 0.7,
            ]);

        if ($chat->failed()) {
            return response()->json(['error' => 'GPT failed.'], 500);
        }

        $reply = $chat->json('choices.0.message.content');

        // 3. ElevenLabs
        $tts = Http::withHeaders([
            'xi-api-key' => env('ELEVENLABS_API_KEY'),
            'Accept' => 'audio/mpeg',
            'Content-Type' => 'application/json',
        ])->post("https://api.elevenlabs.io/v1/text-to-speech/" . env('ELEVENLABS_VOICE_ID'), [
            'text' => $reply,
            'model_id' => 'eleven_multilingual_v2',
            'voice_settings' => [
                'stability' => 0.7,
                'similarity_boost' => 0.9,
            ],
        ]);

        if ($tts->failed()) {
            return response()->json(['error' => 'TTS failed.'], 500);
        }

        $audioFilename = 'ai/' . Str::uuid() . '.mp3';
        Storage::disk('public')->put($audioFilename, $tts->body());
        $audioUrl = asset('storage/' . $audioFilename);

        // 4. Zapis wiadomości
        $now = now();

        $userMessage = $session->messages()->create([
            'sender' => 'user',
            'text' => $transcript,
            'audio_url' => null,
            'timestamp' => $now,
        ]);

        $aiMessage = $session->messages()->create([
            'sender' => 'ai',
            'text' => $reply,
            'audio_url' => $audioUrl,
            'timestamp' => $now->copy()->addSecond(),
        ]);

        // 5. Sprzątanie
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        return response()->json([
            'transcript' => $transcript,
            'text' => $reply,
            'audio_url' => $audioUrl,
            'messages' => [$userMessage, $aiMessage]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/voice-sessions/{id}",
     *     summary="Get all messages for a voice session",
     *     tags={"Voice Sessions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Session messages",
     *         @OA\JsonContent(
     *             @OA\Property(property="session", type="integer"),
     *             @OA\Property(
     *                 property="messages",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="sender", type="string"),
     *                     @OA\Property(property="text", type="string"),
     *                     @OA\Property(property="audio_url", type="string", nullable=true),
     *                     @OA\Property(property="timestamp", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getSessionMessages($sessionId)
    {
        if (!is_numeric($sessionId)) {
            return response()->json(['error' => 'Invalid session ID'], 400);
        }

        $session = VoiceSession::with('messages')->findOrFail($sessionId);

        return response()->json([
            'session' => $session->id,
            'messages' => $session->messages()->orderBy('timestamp')->get(),
        ]);
    }

}
