<?php

namespace App\Services\Automations\Steps;

class StepExecutorFactory
{
    /**
     * Create executor instance based on step type. As of Sept 2020,
     * there are 2 available step types:
     * - delay
     * - action
     * 
     * Update Nov 2021: new decision type are not included since
     *                  it only acts as a router to yes/no path
     *
     * If more to be added, create a new class that implements
     * IStepExecutor and add it to condition below. For how to
     * use the step executor, go to the classes below or refer
     * IStepExecutor
     *
     * @see \App\Services\Automations\Steps\IStepExecutor
     *
     * @param string $stepType
     * @return \App\Services\Automations\Steps\IStepExecutor
     */
    public static function createExecutor(string $stepType): IStepExecutor
    {
        if ($stepType === 'delay') {
            return new DelayStep;
        }

        if ($stepType === 'action') {
            return new ActionStep;
        }
    }
}
