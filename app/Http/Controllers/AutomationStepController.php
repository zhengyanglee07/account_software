<?php

namespace App\Http\Controllers;

use App\Automation;
use App\Services\AutomationStepService;
use App\Traits\AuthAccountTrait;
use Illuminate\Http\Request;
use DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AutomationStepController extends Controller
{
    use AuthAccountTrait;

    private $automationStepServ;

    public function __construct(AutomationStepService $automationStepService)
    {
        $this->automationStepServ = $automationStepService;
    }

    /**
     * Update Nov 2021: now individual steps are created/updated entirely in frontend
     * 
     * @param $automationRefKey
     * @param \Illuminate\Http\Request $request
     */
    public function store($automationRefKey, Request $request)
    {
        $request->validate([
            'steps' => 'required|array'
        ]);

        $currentAccountId = $this->getCurrentAccountId();

        $automation = Automation::findByAccountIdAndRefKey($currentAccountId, $automationRefKey);
        $automation->update(['steps' => $request->steps]);
        
        return response()->noContent();
    }

    /**
     * @param $automationRefKey
     * @param $stepId
     * @param \Illuminate\Http\Request $request
     * @return mixed
     * @throws \Exception
     */
    public function update($automationRefKey, Request $request)
    {
        $request->validate([
            'steps' => 'present|array',
            'prevStep' => 'required',
            'newStep' => 'required'
        ]);

        $steps = $request->steps;
        $prevStep = $request->prevStep;
        $prevKind = $prevStep['kind'];
        $newKind = $request->newStep['kind'];
        $automation = Automation::findByAccountIdAndRefKey($this->getCurrentAccountId(), $automationRefKey);

        // destroy previous kind (delay/action) if new kind is selected
        if ($this->isKindChanges($prevKind, $newKind)) {
            // destroy orphan email
            if ($prevKind === 'automationSendEmailAction') {
                $this->automationStepServ->destroySendEmailEmail($prevStep);
            }
        }

        $automation->update(['steps' => $steps]);

        return response()->noContent();
    }

    private function isKindChanges($kind, $newKind): bool
    {
        return $kind !== $newKind;
    }

    /**
     * Delete automation step
     *
     * @param $automationRefKey
     * @param $stepId
     * @return Response
     * @throws \Throwable
     */
    public function destroy($automationRefKey, Request $request): Response
    {
        $request->validate([
            'step' => 'required',
            'steps' => 'present|array' // updated steps
        ]);

        $step = $request->step;
        $steps = $request->steps;
        $automation = Automation::findByAccountIdAndRefKey($this->getCurrentAccountId(), $automationRefKey);

        DB::transaction(function () use ($step, &$automation, $steps) {
            // special case: delete email associated with send_email action
            if ($this->automationStepServ->isSendEmailStep($step)) {
                $this->automationStepServ->destroySendEmailEmail($step);
            }

            $automation->update(['steps' => $steps]);
        });

        return response()->noContent();
    }

    /**
     * To override the problematic getCurrentAccountId in AuthAccountTrait
     *
     * @return mixed
     */
    private function getCurrentAccountId()
    {
        return Auth::user()->currentAccountId;
    }
}
