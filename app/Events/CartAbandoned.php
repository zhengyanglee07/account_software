<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CartAbandoned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $carts;

    /**
     * Create a new event instance.
     *
     * @param \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection $carts
     */
    public function __construct($carts)
    {
        $this->carts = $carts;
    }
}
