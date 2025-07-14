<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VoiceMessage;
use App\Jobs\GenerateSpeech;

class ExampleUsageController extends Controller
{
    /**
     * Example of how to use the GenerateSpeech job.
     *
     * @param  int  $messageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateSpeechForMessage($messageId)
    {
        // Check if the voice message exists
        $voiceMessage = VoiceMessage::find($messageId);

        if (!$voiceMessage) {
            return response()->json(['error' => 'Voice message not found'], 404);
        }

        // Check if the voice message has text
        if (empty($voiceMessage->text)) {
            return response()->json(['error' => 'Voice message has no text to convert to speech'], 400);
        }

        // Dispatch the job to generate speech
        GenerateSpeech::dispatch($messageId);

        return response()->json([
            'message' => 'Speech generation job has been dispatched',
            'voice_message_id' => $messageId
        ]);
    }

    /**
     * Example of how to listen for the AudioReady event in JavaScript.
     * This is not a PHP method, just an example for documentation.
     *
     * @example
     * // In your JavaScript client code:
     * const channel = Echo.channel('audio');
     * channel.listen('AudioReady', (event) => {
     *     console.log('Audio is ready:', event.audio_url);
     *     console.log('For message ID:', event.message_id);
     *
     *     // Update your UI with the audio player
     *     const audioPlayer = document.getElementById('audio-player');
     *     audioPlayer.src = event.audio_url;
     *     audioPlayer.style.display = 'block';
     * });
     */
}
