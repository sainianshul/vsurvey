<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallAnswered implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callId;
    public $responderId;
    public $responderName;
    public $action; // 'accept' or 'reject'
    public $callerId;

    public function __construct($callId, $responderId, $responderName, $action, $callerId)
    {
        $this->callId        = $callId;
        $this->responderId   = $responderId;
        $this->responderName = $responderName;
        $this->action        = $action;
        $this->callerId      = $callerId;
    }

    public function broadcastOn()
    {
        // Broadcast back to the caller
        return [new PrivateChannel('vyaparmitra_user.' . $this->callerId)];
    }

    public function broadcastAs()
    {
        return 'call-answered';
    }
}
