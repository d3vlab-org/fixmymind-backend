<?php

// === app/Jobs/GenerateReplyJob.php ===
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\OpenAIService;
use App\Models\VoiceMessage;

class GenerateReplyJob implements ShouldQueue
{
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

/**
 * The name of the queue the job should be sent to.
 *
 * @var string
 */
public $queue = 'gpt';

public $message;

public function __construct(VoiceMessage $message)
{
$this->message = $message;
}

public function handle()
{
$context = $this->message->session->context();
$reply = OpenAIService::generateReply($this->message->transcript, $context);

$aiMessage = $this->message->session->messages()->create([
'sender' => 'ai',
'message_text' => $reply,
]);

SynthesizeAudioJob::dispatch($aiMessage);
}
}
