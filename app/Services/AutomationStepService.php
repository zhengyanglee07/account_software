<?php

namespace App\Services;

use App\AutomationAddTagAction;
use App\AutomationDelay;
use App\AutomationRemoveTagAction;
use App\AutomationSendEmailAction;
use App\AutomationStep;
use App\Email;

class AutomationStepService
{
    /**
     * Note: this is for backward-compatibility with previous
     *       automation step impl. To be changed in the future.
     *
     * @param string $type
     * @param string $actionType
     * @return string
     */
    public function generateStepKind(string $type, string $actionType): string
    {
        if ($type === 'delay') return 'automationDelay';
    }

    public function createStepKind(AutomationStep $automationStep, array $properties): void
    {
        $kind = $automationStep->kind;

        if ($kind === 'automationDelay') {
            AutomationDelay::create([
                'automation_step_id' => $automationStep->id,
                'duration' => $properties['duration'],
                'unit' => $properties['unit']
            ]);
            return;
        }

        // note that this just create step kind, it doesn't create
        // a new email.
        if ($kind === 'automationSendEmailAction') {
            AutomationSendEmailAction::create([
                'automation_step_id' => $automationStep->id,
                'email_id' => $properties['email_id']
            ]);
            return;
        }

        if ($kind === 'automationAddTagAction') {
            AutomationAddTagAction::create([
                'automation_step_id' => $automationStep->id,
                'processed_tag_id' => $properties['processed_tag_id']
            ]);
            return;
        }

        if ($kind === 'automationRemoveTagAction') {
            AutomationRemoveTagAction::create([
                'automation_step_id' => $automationStep->id,
                'processed_tag_id' => $properties['processed_tag_id']
            ]);
            return;
        }
    }

    public function isDelayStep(AutomationStep $automationStep): bool
    {
        return $automationStep->kind === 'automationDelay';
    }

    public function isSendEmailStep($step): bool
    {
        return $step['kind'] === 'automationSendEmailAction';
    }

    /**
     * @return mixed
     */
    public function getSendEmailActionsEntities($steps)
    {
        $entities = [];

        if (count($steps) === 0) {
            return $entities;
        }

        foreach ($steps as $step) {
            $stepType = $step['type'];
            $stepId = $step['id'];

            // special case, need to find emails in nested yes/no routes
            if ($stepType === 'decision') {
                $yesPathEntities = $this->getSendEmailActionsEntities($step['yes'] ?? []);
                $noPathEntities = $this->getSendEmailActionsEntities($step['no'] ?? []);
                $entities = array_merge($entities, $yesPathEntities, $noPathEntities);
            }

            if ($stepType !== 'action' || $step['kind'] !== 'automationSendEmailAction') {
                continue;
            }

            // now assuming finally we get to email action
            $email = Email::find($step['properties']['email_id'] ?? '');

            if (!$email) {
                $entities[$stepId] = null;
                continue;
            }

            $entities[$stepId] = [
                'email_reference_key' => $email->reference_key,
                'name' => $email->name,
                'subject' => $email->subject,
                'preview' => $email->preview_text,
                'sender_id' => $email->sender_id,
                'email_design_reference_key' => optional($email->emailDesign)->reference_key,
                'html' => optional($email->emailDesign)->html ?? '',
                'sender_name' => $email->sender_name,
            ];
        }

        return $entities;
    }

    /**
     * Special case for delete step:
     *
     * Delete email & email design if a send email action step is deleted
     *
     * @param array $step
     * @throws \Exception
     */
    public function destroySendEmailEmail($step): void
    {
        $email = Email::find($step['properties']['email_id']);

        if (!$email) {
            return;
        }

        $emailDesign = optional($email)->emailDesign;

        // deletion of email design will cascade to email
        if ($emailDesign) {
            $emailDesign->delete();
            return;
        }

        // delete email directly if email design is absent
        // or not deleting anything if email is not found
        optional($email)->delete();
    }
}
