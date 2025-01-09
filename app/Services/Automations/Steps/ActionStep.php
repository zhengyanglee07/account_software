<?php

namespace App\Services\Automations\Steps;

use App\Services\Automations\Steps\Actions\ActionExecutor;
use App\Services\Automations\Steps\Actions\ActionFactory;

class ActionStep implements IStepExecutor
{
    /**
     * @see \App\Services\Automations\Steps\IStepExecutor
     *
     * @param $triggeredSteps
     * @param int $currentStepIdx
     */
    public function execute($triggeredSteps, int $currentStepIdx): void
    {
        $triggeredStep = $triggeredSteps[$currentStepIdx];

        $actionExecutor = new ActionExecutor(ActionFactory::createAction($triggeredStep->step['kind']));
        $actionExecutor->executeAction($triggeredStep);
    }
}
