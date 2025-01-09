<?php

namespace App\Jobs;

use App\Services\Automations\Steps\StepExecutorFactory;
use App\Services\AutomationService;
use App\TriggeredStep;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ProcessTriggeredSteps implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $errors, $triggeredStep;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\AutomationService $automationService
     * @return void
     */
    public function handle(AutomationService $automationService): void
    {
        $triggeredStepsByBatch = TriggeredStep
            ::all()
            ->groupBy('batch');
        $now = now();

        foreach ($triggeredStepsByBatch as $batch => $triggeredSteps) {
            // in theory there should be at least one triggered step in each batch
            // update Nov 2021: triggered steps should be ordered by default by id
            try {
                foreach ($triggeredSteps as $stepIdx => $triggeredStep) {
                    $this->triggeredStep = $triggeredStep;
                    // null $triggeredStep represents completed step
                    if (!$triggeredStep) {
                        continue;
                    }

                    // skips to next batch if encountered one delayed step
                    //
                    // Note: execute_at property in next steps to a delay will be
                    //       updated immediately in this runtime, so this condition
                    //       will safely skip all steps after a delay step
                    if ($triggeredStep->execute_at > $now) {
                        break;
                    }

                    $automationStep = $triggeredStep->step;
                    $automationStepType = $automationStep['type'];

                    $stepExecutor = StepExecutorFactory::createExecutor($automationStepType);
                    $stepExecutor->execute($triggeredSteps, $stepIdx);

                    $automation = $triggeredStep->automation;
                    if (!$automation->repeat && !$automation->executed) {
                        $automation->update(['executed' => 1]);
                    }

                    // sync triggered processed contact into automation
                    $automation->triggeredContacts()->attach(
                        [$triggeredStep->processed_contact_id => ['triggered_at' => $now]]
                    );

                    $triggeredStep->delete(); // delete current step from queue
                }
            } catch (\Exception $ex) {
                // log and delete remaining steps in a batch if one of the step failed, then continue.
                //
                // Probably this behavior will change, if we want to remain the triggered
                // steps and rerun if error is fixable by user, like choosing a new email if
                // email is missing in send_email action
                //
                // Note: remember throw/rethrow all the errors in steps execution
                Log::error(
                    'Something wrong in processing triggered steps: ' . $ex->getMessage(),
                    [
                        'triggered_step' => $this->triggeredStep ?? [],
                    ]
                );

                $this->errors .= 'ErrorMessage: ' . $ex->getMessage() . '. ' .
                    'TriggeredStep: ' . json_encode($triggeredStep ?? []) . '. ';

                // $automationService->clearTriggeredStepsBatch($triggeredSteps);
            }
        }

        if (empty($this->errors)) return;

        try {
            throw new \Exception($this->errors, 1);
        } catch (\Throwable $th) {
            $this->fail($th);
        }
    }
}
