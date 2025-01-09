<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\ReferralCampaignTrait;

class SendReferralRegisterSuccessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ReferralCampaignTrait;

    public $processedContacts;
    public $campaign;
    public $emailType;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($processedContacts, $campaign, $emailType)
    {
        $this->processedContacts = $processedContacts;
        $this->campaign = $campaign;
        $this->emailType = $emailType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach($this->processedContacts as $campaignProcessedContact) {
            $this->sendNotificationEmail($campaignProcessedContact, $this->campaign, $this->emailType);
        }
    }
}
