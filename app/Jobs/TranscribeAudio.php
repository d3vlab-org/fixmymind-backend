<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\WhisperService;
use App\Models\VoiceMessage;
use App\Events\TranscriptionReceived;

class TranscribeAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public $queue = 'stt';

    /**
     * The voice message instance.
     *
     * @var \App\Models\VoiceMessage
     */
    protected $voiceMessage;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\VoiceMessage  $voiceMessage
     * @return void
     */
    public function __construct(VoiceMessage $voiceMessage)
    {
        $this->voiceMessage = $voiceMessage;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get the audio path from the voice message
        $audioPath = $this->voiceMessage->audio_url;

        // Transcribe the audio using WhisperService
        $text = WhisperService::transcribe($audioPath);

        // Update the voice message with the transcribed text
        $this->voiceMessage->update(['text' => $text]);

        // Broadcast the transcription event
        event(new TranscriptionReceived(
            $this->voiceMessage->voice_session_id,
            $text,
            now()->timestamp
        ));
    }
}
