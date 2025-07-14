<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\VoiceMessage;
use App\Jobs\GenerateSpeech;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;
use App\Events\AudioReady;

class GenerateSpeechTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the GenerateSpeech job can be dispatched.
     *
     * @return void
     */
    public function testGenerateSpeechJobCanBeDispatched()
    {
        Queue::fake();

        // Create a voice message
        $voiceMessage = VoiceMessage::factory()->create([
            'text' => 'This is a test message for speech generation.',
        ]);

        // Dispatch the job
        GenerateSpeech::dispatch($voiceMessage->id);

        // Assert that the job was pushed to the queue
        Queue::assertPushed(GenerateSpeech::class, function ($job) use ($voiceMessage) {
            return $job->messageId === $voiceMessage->id;
        });
    }

    /**
     * Test that the GenerateSpeech job processes correctly.
     *
     * @return void
     */
    public function testGenerateSpeechJobProcessesCorrectly()
    {
        Event::fake();

        // Create a voice message
        $voiceMessage = VoiceMessage::factory()->create([
            'text' => 'This is a test message for speech generation.',
        ]);

        // Process the job
        $job = new GenerateSpeech($voiceMessage->id);
        $job->handle();

        // Refresh the voice message from the database
        $voiceMessage->refresh();

        // Assert that the audio_url was updated
        $this->assertNotNull($voiceMessage->audio_url);

        // Assert that the AudioReady event was dispatched
        Event::assertDispatched(AudioReady::class, function ($event) use ($voiceMessage) {
            return $event->message_id === $voiceMessage->id &&
                   $event->audio_url === $voiceMessage->audio_url;
        });
    }
}
