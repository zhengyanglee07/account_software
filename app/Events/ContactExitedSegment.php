<?php

namespace App\Events;

use App\Segment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ContactExitedSegment
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $segment, $contactIds;
    /**
     * Create a new event instance.
     * 
     * @param Segment $segment
     * @param array $contactIds
     */
    public function __construct(Segment $segment, $contactIds)
    {
        $this->segment = $segment;
        $this->contactIds = $contactIds;
    }
}
