<?php

namespace App\Traits\Automations;

use App\Automation;
use App\AutomationSubmitFormTrigger;

trait TriggerableLandingForm
{
    /**
     * Check if landingPageFormId provided upon trigger matches
     * landing_page_form_id in AutomationSubmitFormTrigger
     *
     * @param Automation $automation
     * @param int $landingPageFormId
     * @return bool
     */
    protected function matchLandingPageFormId($automation, int $landingPageFormId): bool
    {
        return $automation->automationTriggers->contains(function ($at) use ($landingPageFormId) {
            $sfTrigger = $at->automationSubmitFormTrigger;

            if (!$sfTrigger) {
                return false;
            }

            $landingPageForm = $sfTrigger->landingPageForm;
            return is_null($landingPageForm) || $landingPageForm->id === $landingPageFormId;
        });
    }
}
