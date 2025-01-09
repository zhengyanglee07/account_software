<?php

namespace App\Jobs\AutomationTriggers;

use App\AutomationSubmitFormTrigger;
use App\Services\AutomationService;
use App\Services\SegmentService;

/**
 * @deprecated
 *
 * Class TriggerSubmitLandingForm
 * @package App\Jobs\AutomationTriggers
 */
class TriggerSubmitLandingForm extends BaseTrigger
{
    /**
     * @var int
     */
    protected $processedContactId;

    /**
     * @var int
     */
    protected $landingPageFormId;

    /**
     * Create a new job instance.
     *
     * @param int $accountId
     * @param int $processedContactId
     * @param int $landingPageFormId
     */
    public function __construct(int $accountId, int $processedContactId, int $landingPageFormId)
    {
        parent::__construct($accountId, 'submit_form');

        $this->processedContactId = $processedContactId;
        $this->landingPageFormId = $landingPageFormId;
    }

    /**
     * Check if landingPageFormId provided upon trigger is matched with
     * the predefined landing_page_form_id stored in trigger's properties
     *
     * @param AutomationSubmitFormTrigger|null $sfTrigger
     * @return bool
     */
    private function matchLandingPageFormId(?AutomationSubmitFormTrigger $sfTrigger): bool
    {
        if (!$sfTrigger) {
            return false;
        }

        $landingPageForm = $sfTrigger->landingPageForm;
        return is_null($landingPageForm) || $landingPageForm->id === $this->landingPageFormId;
    }

    /**
     * Return true if one of the automation triggers passed the
     * match landing page form id check
     *
     * @param $automation
     * @return mixed
     */
    public function matchTriggersConditions($automation)
    {
        return $automation->automationTriggers->contains(function ($at) {
            return $this->matchLandingPageFormId($at->automationSubmitFormTrigger);
        });
    }

    public function handle(
        AutomationService $automationService,
        SegmentService $segmentService
    ): void
    {
        parent::handle($automationService, $segmentService);

        foreach ($this->automations as $automation) {
            // skip this automation if it's not activated
            if (!$automationService->isAutomationActivated($automation)) {
                continue;
            }

            // skip this automation if it's not executable
            if (!$automationService->isAutomationExecutable($automation)) {
                continue;
            }

            if (!$automationService->isContactInTriggerSegment(
                $automation,
                $this->processedContactId
            )) {
                continue;
            }

            // skip this automation if it doesn't satisfy triggers cond
            if (!$this->matchTriggersConditions($automation)) {
                continue;
            }

            $automationService->createTriggeredSteps($automation, $automation->steps, [
                'processed_contact_id' => $this->processedContactId
            ]);
        }
    }
}
