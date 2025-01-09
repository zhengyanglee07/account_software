<?php

namespace App\Events;

use App\LandingPageForm;
use App\ProcessedContact;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LandingFormSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $landingPageForm;
    public $processedContact;

    /**
     * Create a new event instance.
     *
     * @param LandingPageForm $landingPageForm
     * @param ProcessedContact $processedContact
     */
    public function __construct(LandingPageForm $landingPageForm, ProcessedContact $processedContact)
    {
        $this->landingPageForm = $landingPageForm;
        $this->processedContact = $processedContact;
    }
}
