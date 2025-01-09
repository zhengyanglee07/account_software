<?php

namespace App\Services\Automations\Steps\Actions;

class ActionExecutor
{
    /**
     * @var \App\Services\Automations\Steps\Actions\IActionStrategy
     */
    private $strategy;

    public function __construct(IActionStrategy $actionStrategy)
    {
        $this->strategy = $actionStrategy;
    }

    public function setStrategy(IActionStrategy $actionStrategy): void
    {
        $this->strategy = $actionStrategy;
    }

    public function getStrategy(): IActionStrategy
    {
        return $this->strategy;
    }

    public function executeAction($triggeredStep): void
    {
        $this->strategy->perform($triggeredStep);
    }
}
