<?php

namespace App\Services;

use App\Automation;
use App\ProcessedContact;
use App\Trigger;
use App\TriggeredStep;
use Str;

class AutomationService
{
    /**
     * map front end value to processed_addresses database column
     *
     * @var array
     */
    protected $processedContactMap = [
        'First name' => 'fname',
        'Last name' => 'lname',
        'Email' => 'email',
        'Mobile number' => 'phone_number',
        'Gender' => 'gender',
        'Birth date' => 'birthday',
    ];

    /**
     * map front end value to processed_contacts database column
     *
     * @var array
     */
    protected $processedAddressMap = [
        'Address 1' => 'address1',
        'Address 2' => 'address2',
        'Postcode' => 'zip',
        'City' => 'city',
        'State' => 'state',
        'Country' => 'country',
    ];

    /**
     * Determine whether an automation is triggerable
     *
     * @param Automation $automation
     * @param ProcessedContact $processedContact
     * @return bool
     */
    public function isAutomationTriggerable($automation, ProcessedContact $processedContact): bool
    {
        return
            $this->isAutomationActivated($automation) &&
            $this->isAutomationExecutable($automation) &&
            $this->isContactInTriggerSegment($automation, $processedContact->id);
    }

    /**
     * Simply determine whether the automation is under "activated" status
     *
     * @param Automation $automation
     * @return bool
     */
    public function isAutomationActivated($automation): bool
    {
        return $automation->automationStatus->name === 'activated';
    }

    /**
     * An automation is executable if it's under following conditions:
     * - repeat is set to 1 (true), this ignores executed
     * - not executed before (executed === 0)
     *
     * @param \App\Automation $automation
     * @return bool
     */
    public function isAutomationExecutable($automation): bool
    {
        return (bool)$automation->repeat ?: !$automation->executed;
    }

    /**
     * Check whether trigger contact included in segment saved in automation_trigger
     *
     * @param Automation $automation
     * @param int $processedContactId
     * @return bool
     */
    public function isContactInTriggerSegment($automation, int $processedContactId): bool
    {
        return $automation->automationTriggers->contains(function ($at) use ($processedContactId) {
            $segment = $at->segment;

            // null segment_id matches every contacts
            return !isset($segment) ?: $segment->hasContact($processedContactId);
        });
    }

    /**
     * Return a closure func which continue to build query
     * that matches provided $triggerId
     *
     * @param int $triggerId
     * @return callable
     */
    private function matchesTriggerId(int $triggerId): callable
    {
        return static function ($query) use ($triggerId) {
            $query->where('trigger_id', $triggerId);
        };
    }

    /**
     * Get/filter current account triggered automations based on triggerType.
     *
     * In addition, eager load only the related automation_triggers, such as
     * for submit_form type, only submit_form automation_triggers will be
     * eager loaded.
     *
     * @param string $triggerType
     * @param int $accountId
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getTriggeredAutomations(string $triggerType, int $accountId)
    {
        $triggerId = Trigger::where('type', $triggerType)->firstOrFail()->id;

        return Automation::where('account_id', $accountId)
            ->with([
                'automationTriggers' => $this->matchesTriggerId($triggerId)
            ])
            ->whereHas('automationTriggers', $this->matchesTriggerId($triggerId))
            ->get();
    }

    /**
     * Variant of getTriggeredAutomations() without accountId limitation.
     *
     * @param string $triggerType
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getEveryTriggeredAutomations(string $triggerType)
    {
        $triggerId = Trigger::where('type', $triggerType)->firstOrFail()->id;

        return Automation
            ::with([
                'automationTriggers' => $this->matchesTriggerId($triggerId)
            ])
            ->whereHas('automationTriggers', $this->matchesTriggerId($triggerId))
            ->get();
    }

    /**
     * To decide whether yes/no path to proceed in Decision step
     * Return true for yes path, else no
     *
     * @return bool
     */
    public function checkDecisionTruthy($step, $processedContactId)
    {
        $processedContact = ProcessedContact::find($processedContactId);

        // assuming falsy if contact can't be found in triggered step
        if (!$processedContact) {
            return false;
        }

        $filters = $step['properties']['filters'];

        foreach ($filters as $ORFilter) {
            $ANDResults = [];
            foreach ($ORFilter as $ANDIdx => $ANDFilter) {
                $filterName = $ANDFilter['name'];
                $subFilters = $ANDFilter['subFilters'];

                // Tags filter
                if ($filterName === 'Tags') {
                    $isContain = $subFilters[0]['value'] === 'contain';
                    $tagValue = $subFilters[1]['value'];
                    $containTagValue = $processedContact->processed_tags->pluck('tagName')->contains($tagValue);
                    $ANDResults[$ANDIdx] = $isContain ? $containTagValue : !$containTagValue;
                    continue;
                }

                // Custom Field filter
                if ($filterName === 'Custom Field') {
                    $contactCustomFields = $processedContact->peopleCustomFields;
                    $selectedCustomFieldName = $subFilters[0]['value'];
                    $comparisonOperator = $subFilters[1]['value'];
                    $comparisonValue = $subFilters[2]['value'] ?? null;

                    $customFieldNames = $contactCustomFields->pluck('peopleCustomFieldName.custom_field_name');
                    $customFieldNameValues = $customFieldNames->pluck('custom_field_name');

                    if ($comparisonOperator === 'is set' || $comparisonOperator === 'is not set') {
                        $customFieldIsSet = $customFieldNameValues->contains($selectedCustomFieldName);
                        $ANDResults[$ANDIdx] = $comparisonOperator === 'is set' ? $customFieldIsSet : !$customFieldIsSet;
                        continue;
                    }

                    if ($comparisonOperator === 'is' || $comparisonOperator === 'is not') {
                        $ANDResults[$ANDIdx] = $customFieldNameValues->contains(function ($v) use ($comparisonOperator, $comparisonValue) {
                            return $comparisonOperator === 'is' ? $v === $comparisonValue : $v !== $comparisonValue;
                        });
                        continue;
                    }

                    $lte = 'is less than or equal to';
                    $gte = 'is greater than or equal to';
                    if ($comparisonOperator === $lte || $comparisonOperator === $gte) {
                        $ANDResults[$ANDIdx] = $customFieldNameValues->contains(function ($v) use ($comparisonOperator, $comparisonValue, $lte) {
                            return $comparisonOperator === $lte ? $v <= $comparisonValue : $v >= $comparisonValue;
                        });
                        continue;
                    }
                }

                // People Profile filter
                if ($filterName === 'People Profile') {
                    $selectedPeopleProfile = $subFilters[0]['value'];
                    $comparisonOperator = $subFilters[1]['value'];
                    $comparisonValue = $subFilters[2]['value'] ?? null;

                    $column = ($this->processedContactMap[$selectedPeopleProfile] ?? null)
                        ??
                        ($this->processedAddressMap[$selectedPeopleProfile] ?? null);

                    $peopleProfileValue = in_array($column, $this->processedContactMap)
                        ? $processedContact->$column
                        : optional($processedContact->addresses->first())->$column;

                    if ($comparisonOperator === 'is set' || $comparisonOperator === 'is not set') {
                        $ANDResults[$ANDIdx] = $comparisonOperator === 'is set' ? !is_null($peopleProfileValue) : is_null($peopleProfileValue);
                        continue;
                    }

                    if ($comparisonOperator === 'is' || $comparisonOperator === 'is not') {
                        $equal = strcasecmp($peopleProfileValue, $comparisonValue);
                        $ANDResults[$ANDIdx] = $comparisonOperator === 'is' ? $equal === 0 : $equal !== 0;
                        continue;
                    }
                }
            }

            $isAllTrue = !in_array(false, $ANDResults, true);

            // no need to continue another OR since one truthy AND is enough
            // for OR to be true
            if ($isAllTrue) {
                return true;
            }

            $ANDResults = [];
        }

        // if code reaches here it should mean all AND filters results are false
        return false;
    }

    /**
     * Create triggered steps based on automation steps.
     *
     * Note: newly added bool return value. If false is returned, it indicates
     *       a ceased operation
     *
     * extraProps is an optional arguments letting you provide
     * additional information that only known upon trigger time,
     * such as the id of processed_contact which triggers the automation
     *
     * @param \App\Automation $automation
     * @param array|null $extraProps
     */
    public function createTriggeredSteps($automation, $steps, ?array $extraProps = null, ?string $batch = null): bool
    {
        $batchRand = $batch ?? Str::random();

        foreach ($steps as $step) {
            $stepType = $step['type'];
            $processedContactId = $extraProps['processed_contact_id'] ?? null;

            if ($stepType === 'decision') {
                $yesOrNoSteps = $this->checkDecisionTruthy($step, $processedContactId)
                    ? $step['yes']
                    : $step['no'];

                $exit = !$this->createTriggeredSteps($automation, $yesOrNoSteps, $extraProps, $batchRand);

                if ($exit) {
                    return false;
                } else {
                    continue;
                }
            }

            // terminate directly regardless of nesting level if exit
            if ($stepType === 'exit') {
                return false;
            }

            // just create step linearly if step isn't decision
            TriggeredStep::create([
                'automation_id' => $automation->id,
                'processed_contact_id' => $processedContactId,
                'batch' => $batchRand,
                'step' => $step,
                'execute_at' => now()
            ]);
        }

        return true;
    }

    /**
     * Clear all triggered steps in batch
     *
     * @param $triggeredSteps
     */
    public function clearTriggeredStepsBatch($triggeredSteps): void
    {
        foreach ($triggeredSteps as $triggeredStep) {
            if ($triggeredStep) {
                $triggeredStep->delete();
            }
        }
    }
}
