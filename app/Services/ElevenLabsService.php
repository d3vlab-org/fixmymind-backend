<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ElevenLabsService
{
    public static function synthesize($text)
    {
        $response = Http::withHeaders([
            'xi-api-key' => config('services.elevenlabs.key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.elevenlabs.io/v1/text-to-speech/' . config('services.elevenlabs.voice_id'), [
            'text' => $text,
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.75
            ]
        ]);

        $filename = 'ai_' . uniqid() . '.mp3';
        file_put_contents(storage_path('app/public/audio/' . $filename), $response->body());

        return asset('storage/audio/' . $filename);
    }
}
