<?php

namespace App\Services\Automations\Steps\Actions;

use App\TriggeredStep;

interface IActionStrategy
{
    /**
     * Perform the action strategy.
     *
     * @param \App\TriggeredStep $triggeredStep
     */
    public function perform(TriggeredStep $triggeredStep): void;
}
