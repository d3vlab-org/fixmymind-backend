<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ElevenLabsService;
use App\Models\VoiceMessage;
use App\Events\AudioReady;

class GenerateSpeech implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public $queue = 'tts';

    /**
     * The voice message ID.
     *
     * @var int
     */
    public $messageId;

    /**
     * Create a new job instance.
     *
     * @param int $messageId
     * @return void
     */
    public function __construct(int $messageId)
    {
        $this->messageId = $messageId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get the voice message
        $voiceMessage = VoiceMessage::findOrFail($this->messageId);

        // Get the text from the voice message
        $text = $voiceMessage->text;

        // Generate audio using ElevenLabsService
        $audioUrl = ElevenLabsService::synthesize($text);

        // Update the voice message with the audio URL
        $voiceMessage->update(['audio_url' => $audioUrl]);

        // Broadcast the audio ready event
        event(new AudioReady(
            $voiceMessage->id,
            $audioUrl
        ));
    }
}
