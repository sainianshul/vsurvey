<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallSignal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callId;
    public $senderId;
    public $targetUserId;
    public $signalType; // offer, answer, ice-candidate
    public $signalData;

    public function __construct($callId, $senderId, $targetUserId, $signalType, $signalData)
    {
        $this->callId       = $callId;
        $this->senderId     = $senderId;
        $this->targetUserId = $targetUserId;
        $this->signalType   = $signalType;
        $this->signalData   = $signalData;
    }

    public function broadcastOn()
    {
        return [new PrivateChannel('vyaparmitra_user.' . $this->targetUserId)];
    }

    public function broadcastAs()
    {
        return 'call-signal';
    }
}
