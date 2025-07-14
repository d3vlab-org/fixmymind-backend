<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\OpenAIService;
use App\Models\VoiceSession;
use App\Models\VoiceMessage;
use App\Events\AiResponseReady;
use Illuminate\Support\Facades\Log;

class GenerateAiResponse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public $queue = 'gpt';

    /**
     * The session ID.
     *
     * @var int
     */
    protected $session_id;

    /**
     * Create a new job instance.
     *
     * @param int $session_id
     * @return void
     */
    public function __construct(int $session_id)
    {
        $this->session_id = $session_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Find the session
            $session = VoiceSession::findOrFail($this->session_id);

            // Get recent messages from the session
            $messages = $session->messages()
                ->orderBy('created_at', 'desc')
                ->take(10)  // Limit to last 10 messages for context
                ->get()
                ->reverse(); // Reverse to get chronological order

            // Check if we have any messages
            if ($messages->isEmpty()) {
                Log::warning("No messages found for session {$this->session_id}");
                return;
            }

            // Format messages for OpenAI
            $context = $messages->map(function ($msg) {
                return [
                    'sender' => $msg->sender,
                    'message_text' => $msg->text
                ];
            })->toArray();

            // Find the last user message to use as input
            $lastUserMessage = '';
            foreach (array_reverse($context) as $msg) {
                if ($msg['sender'] === 'user') {
                    $lastUserMessage = $msg['message_text'];
                    break;
                }
            }

            // Generate AI response using OpenAI
            $aiResponse = OpenAIService::generateReply($lastUserMessage, $context);

            // Create a new voice message with the AI response
            $aiMessage = $session->messages()->create([
                'sender' => 'ai',
                'text' => $aiResponse,
                'timestamp' => now()->timestamp,
            ]);

            // Emit the AiResponseReady event
            event(new AiResponseReady(
                $this->session_id,
                $aiResponse,
                now()->timestamp
            ));

            Log::info("AI response generated for session {$this->session_id}");

        } catch (\Exception $e) {
            Log::error("Error generating AI response: " . $e->getMessage());
            throw $e;
        }
    }
}
