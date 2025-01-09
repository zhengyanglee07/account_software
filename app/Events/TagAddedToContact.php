<?php

namespace App\Events;

use App\ProcessedTag;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TagAddedToContact
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $processedTag;
    public $processedContactIds;

    /**
     * Create a new event instance.
     *
     * @param ProcessedTag $processedTag
     * @param array $processedContactIds
     */
    public function __construct(ProcessedTag $processedTag, array $processedContactIds)
    {
        $this->processedTag = $processedTag;
        $this->processedContactIds = $processedContactIds;
    }
}
