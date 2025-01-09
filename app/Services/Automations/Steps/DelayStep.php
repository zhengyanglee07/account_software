<?php

namespace App\Services\Automations\Steps;

use Carbon\Carbon;

class DelayStep implements IStepExecutor
{
    private function addDurations($properties): string
    {
        $duration = $properties['duration'];
        $unit = $properties['unit'];
        $now = Carbon::now();

        if ($unit === 'minute') {
            $now->addMinutes($duration);
        } else if ($unit === 'hour') {
            $now->addHours($duration);
        } else if ($unit === 'day') {
            $now->addDays($duration);
        }

        return $now->toDateTimeString();
    }

    /**
     * @see \App\Services\Automations\Steps\IStepExecutor
     *
     * @param $triggeredSteps
     * @param int $currentStepIdx
     */
    public function execute($triggeredSteps, int $currentStepIdx): void
    {
        $triggeredStep = $triggeredSteps[$currentStepIdx];

        $nextStepIdx = $currentStepIdx + 1;
        $triggeredStepsCount = count($triggeredSteps);

        // idx out of bound
        if ($nextStepIdx >= $triggeredStepsCount) {
            return;
        }

        $date = $this->addDurations($triggeredStep->step['properties']);

        for ($i = $nextStepIdx; $i < $triggeredStepsCount; $i++) {
            $step = $triggeredSteps[$i];

            // this execute_at update will take effect immediately
            // no need to re-retrieve from db
            $step->update([
                'execute_at' => $date
            ]);
        }
    }
}
