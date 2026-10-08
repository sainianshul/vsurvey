<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CallInitiated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callId;
    public $callerId;
    public $callerName;
    public $callerAvatar;
    public $callType;     // 'audio' or 'video'
    public $receiverId;
    public $productId;
    public $requirementId;

    public function __construct($callId, $callerId, $callerName, $callerAvatar, $callType, $receiverId, $productId = null, $requirementId = null)
    {
        $this->callId       = $callId;
        $this->callerId     = $callerId;
        $this->callerName   = $callerName;
        $this->callerAvatar = $callerAvatar;
        $this->callType     = $callType;
        $this->receiverId   = $receiverId;
        $this->productId    = $productId;
        $this->requirementId = $requirementId;

        Log::info("CallInitiated event fired", [
            'call_id'     => $this->callId,
            'caller_id'   => $this->callerId,
            'receiver_id' => $this->receiverId,
        ]);
    }

    public function broadcastOn()
    {
        return [new PrivateChannel('vyaparmitra_user.' . $this->receiverId)];
    }

    public function broadcastAs()
    {
        return 'incoming-call';
    }
}
