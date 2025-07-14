<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TranscriptionReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The session ID.
     *
     * @var string
     */
    public $session_id;

    /**
     * The transcribed text.
     *
     * @var string
     */
    public $text;

    /**
     * The timestamp.
     *
     * @var int
     */
    public $timestamp;

    /**
     * Create a new event instance.
     *
     * @param string $session_id
     * @param string $text
     * @param int $timestamp
     * @return void
     */
    public function __construct(string $session_id, string $text, int $timestamp)
    {
        $this->session_id = $session_id;
        $this->text = $text;
        $this->timestamp = $timestamp;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel("session.{$this->session_id}");
    }
}
