<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TriggerDateReached
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $date;

    /**
     * Create a new event instance.
     *
     * @param $date
     */
    public function __construct($date)
    {
        $this->date = $date;
    }
}
