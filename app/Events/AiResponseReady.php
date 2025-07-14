<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AiResponseReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The session ID.
     *
     * @var int
     */
    public $session_id;

    /**
     * The AI response text.
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
     * @param int $session_id
     * @param string $text
     * @param int $timestamp
     * @return void
     */
    public function __construct(int $session_id, string $text, int $timestamp)
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
