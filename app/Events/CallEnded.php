<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallEnded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callId;
    public $endedBy;
    public $reason;
    public $targetUserId;
    public $durationSeconds;

    public function __construct($callId, $endedBy, $reason, $targetUserId, $durationSeconds = null)
    {
        $this->callId          = $callId;
        $this->endedBy         = $endedBy;
        $this->reason          = $reason;
        $this->targetUserId    = $targetUserId;
        $this->durationSeconds = $durationSeconds;
    }

    public function broadcastOn()
    {
        return [new PrivateChannel('vyaparmitra_user.' . $this->targetUserId)];
    }

    public function broadcastAs()
    {
        return 'call-ended';
    }
}
