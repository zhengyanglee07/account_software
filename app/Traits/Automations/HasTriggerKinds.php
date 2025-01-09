<?php

namespace App\Traits\Automations;

use App\AutomationAbandonCartTrigger;
use App\AutomationAddTagTrigger;
use App\AutomationDateBasedTrigger;
use App\AutomationEnterSegmentTrigger;
use App\AutomationExitSegmentTrigger;
use App\AutomationOrderSpentTrigger;
use App\AutomationPlaceOrderTrigger;
use App\AutomationPurchaseProductTrigger;
use App\AutomationRemoveTagTrigger;
use App\AutomationSubmitFormTrigger;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Simple trait storing all trigger kinds needed by automation trigger
 *
 * Trait HasTriggerKinds
 * @package App\Traits\Automations
 */
trait HasTriggerKinds
{
    public function automationSubmitFormTrigger(): HasOne
    {
        return $this->hasOne(AutomationSubmitFormTrigger::class);
    }

    public function automationPurchaseProductTrigger(): HasOne
    {
        return $this->hasOne(AutomationPurchaseProductTrigger::class);
    }

    public function automationDateBasedTrigger(): HasOne
    {
        return $this->hasOne(AutomationDateBasedTrigger::class);
    }

    public function automationAddTagTrigger(): HasOne
    {
        return $this->hasOne(AutomationAddTagTrigger::class);
    }

    public function automationRemoveTagTrigger(): HasOne
    {
        return $this->hasOne(AutomationRemoveTagTrigger::class);
    }

    public function automationOrderSpentTrigger(): HasOne
    {
        return $this->hasOne(AutomationOrderSpentTrigger::class);
    }

    public function automationAbandonCartTrigger(): HasOne
    {
        return $this->hasOne(AutomationAbandonCartTrigger::class);
    }

    public function automationPlaceOrderTrigger()
    {
        return $this->hasOne(AutomationPlaceOrderTrigger::class);
    }
    public function automationEnterSegmentTrigger()
    {
        return $this->hasOne(AutomationEnterSegmentTrigger::class);
    }
    public function automationExitSegmentTrigger()
    {
        return $this->hasOne(AutomationExitSegmentTrigger::class);
    }
}