<?php

namespace App\Observers;

use App\ProcessedContactPrize;
use App\ProcessedContact;
use App\ReferralCampaignPrize;
use App\ReferralCampaign;
use App\Traits\ReferralCampaignTrait;

class ProcessedContactPrizeObserver
{
    use ReferralCampaignTrait;
    /**
     * Handle the processed contact prize "created" event.
     *
     * @param  \App\ProcessedContactPrize
     * @return void
     */
    public function created(ProcessedContactPrize $winner)
    {
        logger($winner);

        $processedContact = ProcessedContact::find($winner->processed_contact_id);
        $referralCampaignPrize = ReferralCampaignPrize::find($winner->prize_id);
        $campaign = ReferralCampaign::find($referralCampaignPrize->referral_campaign_id);

        $this->sendNotificationEmail($processedContact, $campaign, 'grand-prize');
    }
}
