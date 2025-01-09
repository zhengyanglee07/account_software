<?php

namespace App\Jobs\AutomationTriggers;

use App\Services\AutomationService;
use App\Services\AutomationTriggerService;
use App\Services\SegmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * @deprecated
 *
 * Class BaseTrigger
 * @package App\Jobs\AutomationTriggers
 */
abstract class BaseTrigger implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $accountId;

    /**
     * @var string
     */
    protected $triggerType;

    /**
     * @var \App\Automation[]|\Illuminate\Database\Eloquent\Collection
     */
    protected $automations;

    /**
     * @var \App\Services\SegmentService
     */
    protected $segmentService;

    /**
     * Create a new job instance.
     *
     * @param int $accountId
     * @param string $triggerType
     */
    public function __construct(int $accountId, string $triggerType)
    {
        $this->accountId = $accountId;
        $this->triggerType = $triggerType;
    }

    /**
     * Execute the job.
     *
     * Here contains all initializations of common properties, child class
     * of this BaseTrigger class should always override this method
     *
     * @param \App\Services\AutomationService $automationService
     * @param \App\Services\SegmentService $segmentService
     * @return void
     */
    public function handle(
        AutomationService $automationService,
        SegmentService $segmentService
    ): void
    {
        $this->automations = $automationService->getTriggeredAutomations(
            $this->triggerType,
            $this->accountId
        );

        $this->segmentService = $segmentService;
    }
}
