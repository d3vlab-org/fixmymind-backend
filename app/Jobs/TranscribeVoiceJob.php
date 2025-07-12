<?php

// === app/Jobs/TranscribeVoiceJob.php ===
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\WhisperService;
use App\Models\VoiceMessage;

class TranscribeVoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $message;

    public function __construct(VoiceMessage $message)
    {
        $this->message = $message;
    }

    public function handle()
    {
        $transcript = WhisperService::transcribe($this->message->audio_path);
        $this->message->update(['transcript' => $transcript]);

        GenerateReplyJob::dispatch($this->message);
    }
}

