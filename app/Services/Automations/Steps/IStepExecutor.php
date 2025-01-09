<?php

namespace App\Services\Automations\Steps;

interface IStepExecutor
{
    /**
     * Execute a step from triggered steps. This function
     * or method will serve as the main function to call
     * when a step is executed. Implementor of this interface
     * can add as much helper func as they want, as long as
     * this method exists.
     *
     * @param $triggeredSteps
     * @param int $currentStepIdx
     */
    public function execute($triggeredSteps, int $currentStepIdx): void;
}
