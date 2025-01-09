<?php

namespace App\Services;

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
use App\AutomationTrigger;
use App\Trigger;

class AutomationTriggerService
{
    private const SUBMIT_FORM_KIND = 'automationSubmitFormTrigger';
    private const PURCHASE_PRODUCT_KIND = 'automationPurchaseProductTrigger';
    private const DATE_BASED_KIND = 'automationDateBasedTrigger';
    private const ADD_TAG_KIND = 'automationAddTagTrigger';
    private const REMOVE_TAG_KIND = 'automationRemoveTagTrigger';
    private const ORDER_SPENT_KIND = 'automationOrderSpentTrigger';
    private const ABANDON_CART_KIND = 'automationAbandonCartTrigger';
    private const PLACE_ORDER_KIND = 'automationPlaceOrderTrigger';
    private const ENTER_SEGMENT_KIND = 'automationEnterSegmentTrigger';
    private const EXIT_SEGMENT_KIND = 'automationExitSegmentTrigger';

    /**
     * @param Trigger $trigger
     * @return string
     */
    public function generateTriggerKind(Trigger $trigger): string
    {
        $triggerType = $trigger->type;

        if ($triggerType === 'submit_form') {
            return self::SUBMIT_FORM_KIND;
        }

        if ($triggerType === 'purchase_product') {
            return self::PURCHASE_PRODUCT_KIND;
        }

        if ($triggerType === 'date_based') {
            return self::DATE_BASED_KIND;
        }

        if ($triggerType === 'add_tag') {
            return self::ADD_TAG_KIND;
        }

        if ($triggerType === 'remove_tag') {
            return self::REMOVE_TAG_KIND;
        }

        if ($triggerType === 'order_spent') {
            return self::ORDER_SPENT_KIND;
        }

        if ($triggerType === 'abandon_cart') {
            return self::ABANDON_CART_KIND;
        }

        if ($triggerType === 'place_order') {
            return self::PLACE_ORDER_KIND;
        }

        if ($triggerType === 'enter_segment') {
            return self::ENTER_SEGMENT_KIND;
        }

        if ($triggerType === 'exit_segment') {
            return self::EXIT_SEGMENT_KIND;
        }

        throw new \RuntimeException('Trigger type not found. Please check your trigger provided.');
    }

    /**
     * @param AutomationTrigger $automationTrigger
     * @param array|null $properties
     * @throws \JsonException
     */
    public function createTriggerKind(AutomationTrigger $automationTrigger, ?array $properties = []): void
    {
        $kind = $automationTrigger->kind;
        
        if ($kind === self::SUBMIT_FORM_KIND) {
            AutomationSubmitFormTrigger::create([
                'automation_trigger_id' => $automationTrigger->id,
                'landing_page_form_id' => $properties['landing_page_form_id']
            ]);
            return;
        }

        if ($kind === self::PURCHASE_PRODUCT_KIND) {
            AutomationPurchaseProductTrigger::create([
                'automation_trigger_id' => $automationTrigger->id,
                'users_product_id' => $properties['users_product_id']
            ]);
            return;
        }

        if ($kind === self::DATE_BASED_KIND) {
            AutomationDateBasedTrigger::create([
                'automation_trigger_id' => $automationTrigger->id,
                'execution_time_period' => $properties['execution_time_period'],
                'execution_time_unit' => $properties['execution_time_unit'],
                'execution_time_direction' => $properties['execution_time_direction'],
                'target' => $properties['target'],
                'target_specific_date' => $properties['target_specific_date'],
                'repeat_yearly' => $properties['repeat_yearly']
            ]);
            return;
        }

        if ($kind === self::ADD_TAG_KIND) {
            AutomationAddTagTrigger::create([
                'automation_trigger_id' => $automationTrigger->id,
                'processed_tag_id' => $properties['processed_tag_id']
            ]);
            return;
        }

        if ($kind === self::REMOVE_TAG_KIND) {
            AutomationRemoveTagTrigger::create([
                'automation_trigger_id' => $automationTrigger->id,
                'processed_tag_id' => $properties['processed_tag_id']
            ]);
            return;
        }

        if ($kind === self::ORDER_SPENT_KIND) {
            AutomationOrderSpentTrigger::create([
                'automation_trigger_id' => $automationTrigger->id,
                'operator' => $properties['operator'],
                'spent' => $properties['spent']
            ]);
        }

        if ($kind === self::ABANDON_CART_KIND) {
            AutomationAbandonCartTrigger::create([
                'automation_trigger_id' => $automationTrigger->id,
            ]);
        }

        if ($kind === self::PLACE_ORDER_KIND) {
            AutomationPlaceOrderTrigger::create([
                'automation_trigger_id' => $automationTrigger->id,
                'filters' => $properties['filters'],
            ]);
        }

        if ($kind === self::ENTER_SEGMENT_KIND) {
            AutomationEnterSegmentTrigger::create([
                'automation_trigger_id' => $automationTrigger->id,
                'segment_id' => $properties['segment_id'],
            ]);
        }
        
        if ($kind === self::EXIT_SEGMENT_KIND) {
            AutomationExitSegmentTrigger::create([
                'automation_trigger_id' => $automationTrigger->id,
                'segment_id' => $properties['segment_id'],
            ]);
        }
    }
}