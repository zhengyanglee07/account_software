<?php

namespace App\Http\Controllers;

use App\Automation;
use App\AutomationTrigger;
use App\Services\AutomationTriggerService;
use App\Traits\AuthAccountTrait;
use App\Trigger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;

class AutomationTriggerController extends Controller
{
    use AuthAccountTrait;

    private $automationTriggerService;

    public function __construct(AutomationTriggerService $automationTriggerService)
    {
        $this->automationTriggerService = $automationTriggerService;
    }

    /**
     * Store new entry to automation_trigger table
     *
     * @param int|string $referenceKey
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function store($referenceKey, Request $request): JsonResponse
    {
        $request->validate([
            'triggerId' => 'required|integer',
            'properties' => 'nullable|array',
            'segmentId' => 'nullable|integer'
        ]);

        $automation = Automation
            ::where('account_id', $request->user()->currentAccountId)
            ->where('reference_key', $referenceKey)
            ->firstOrFail();

        DB::transaction(function () use ($automation, $request) {
            $triggerId = $request->triggerId;
            $properties = $request->properties;
            $segmentId = $request->segmentId;

            $kind = $this->automationTriggerService->generateTriggerKind(Trigger::find($triggerId));

            $automationTrigger = AutomationTrigger::create([
                'automation_id' => $automation->id,
                'trigger_id' => $triggerId,
                'segment_id' => $segmentId,
                'kind' => $kind,
            ]);

            $this->automationTriggerService->createTriggerKind($automationTrigger, $properties);
        });

        return response()->json([
            'automationTriggers' => $automation->automationTriggers
        ]);
    }

    /**
     * Update an entry in automation_trigger table
     *
     * @param $referenceKey
     * @param AutomationTrigger $automationTrigger
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     * @throws \Throwable
     */
    public function update($referenceKey, AutomationTrigger $automationTrigger, Request $request): JsonResponse
    {
        $triggerId = $request->triggerId;
        $properties = $request->properties;
        $segmentId = $request->segmentId;

        $newKind = $this->automationTriggerService->generateTriggerKind(Trigger::find($triggerId));

        // kind changed
        if ($automationTrigger->kind !== $newKind) {
            DB::transaction(function () use ($automationTrigger, $newKind, $properties) {
                $automationTrigger->triggerKind->delete();
                $automationTrigger->kind = $newKind;
                $this->automationTriggerService->createTriggerKind($automationTrigger, $properties);
                $automationTrigger->save();
            });
        }

        $automationTrigger->update([
            'trigger_id' => $triggerId,
            'segment_id' => $segmentId
        ]);

        $automationTrigger->triggerKind->update($properties ?? []);

        return response()->json([
            'automationTrigger' => $automationTrigger->refresh()
        ]);
    }

    /**
     * Delete an entry from automation_trigger table
     *
     * @param int $referenceKey Automation reference_key
     * @param AutomationTrigger $automationTrigger
     * @return Response
     * @throws \Exception
     */
    public function destroy($referenceKey, AutomationTrigger $automationTrigger): Response
    {
        $automationTrigger->delete();
        return response()->noContent();
    }
}
