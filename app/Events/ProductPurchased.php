<?php

namespace App\Events;

use App\Order;
use App\ProcessedContact;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductPurchased
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $processedContact;

    /**
     * Create a new event instance.
     *
     * @param Order $order
     * @param ProcessedContact $processedContact
     */
    public function __construct(Order $order, ProcessedContact $processedContact)
    {
        $this->order = $order;
        $this->processedContact = $processedContact;
    }
}
