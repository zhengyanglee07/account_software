<?php

namespace App\Jobs\AutomationTriggers;

use App\AutomationPurchaseProductTrigger;
use App\Order;
use App\Services\AutomationService;
use App\Services\SegmentService;

/**
 * @deprecated
 *
 * Class TriggerPurchaseProduct
 * @package App\Jobs\AutomationTriggers
 */
class TriggerPurchaseProduct extends BaseTrigger
{
    /**
     * @var int
     */
    protected $processedContactId;

    /**
     * @var \App\Order
     */
    protected $order;

    /**
     * Create a new job instance.
     *
     * @param int $accountId
     * @param int $processedContactId
     * @param \App\Order $order
     */
    public function __construct(int $accountId, int $processedContactId, Order $order)
    {
        parent::__construct($accountId, 'purchase_product');

        $this->processedContactId = $processedContactId;
        $this->order = $order;
    }

    /**
     * Check if usersProductId provided upon trigger matches the
     * the predefined users_product_id stored in trigger's properties
     *
     * @param AutomationPurchaseProductTrigger|null $ppTrigger
     * @return bool
     */
    private function matchUsersProductId(?AutomationPurchaseProductTrigger $ppTrigger): bool
    {
        if (!$ppTrigger) {
            return false;
        }

        $usersProductId = $ppTrigger->users_product_id;
        $usersProductIds = $this->order->orderDetails->pluck('users_product_id');

        // null usersProductId means "Any" product, which matches any usersProduct
        return is_null($usersProductId) || $usersProductIds->contains($usersProductId);
    }

    /**
     * Return true if one of the automation triggers matches usersProductId
     * provided on trigger time
     *
     * @param $automation
     * @return bool
     */
    public function matchTriggersConditions($automation): bool
    {
        return $automation->automationTriggers->contains(function ($at) {
            return $this->matchUsersProductId($at->automationPurchaseProductTrigger);
        });
    }

    public function handle(
        AutomationService $automationService,
        SegmentService $segmentService
    ): void {
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
