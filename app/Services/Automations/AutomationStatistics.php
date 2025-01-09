<?php

namespace App\Services\Automations;

use App\AutomationStatus;
use App\Models\EmailReport;
use App\TriggeredStep;
use Carbon\Carbon;
use Error;
use RuntimeException;

class AutomationStatistics
{

	protected $trigerredSteps, $triggeredStepGroupByBatch, $triggeredStepGroupByStep;
	public $triggeredStepProgressGroupByBatch, $triggeredStepProgressGroupByStep;

	public function __construct(protected int $automationId)
	{
		$this->trigerredSteps = TriggeredStep::withTrashed()->where('automation_id', $automationId);

		$this->triggeredStepGroupByBatch = $this->trigerredSteps->select(['batch', 'step', 'execute_at', 'deleted_at'])->get()->groupBy('batch');
		$this->triggeredStepProgressGroupByBatch = $this->getStatistics($this->triggeredStepGroupByBatch);

		$this->triggeredStepGroupByStep = $this->trigerredSteps->select(['step', 'execute_at', 'deleted_at', 'step->id as step_id'])->get()->groupBy('step_id');
		$this->triggeredStepProgressGroupByStep = $this->getStatistics($this->triggeredStepGroupByStep);
	}

	private function getStatistics($groupedStep)
	{
		$statistics = [];
		foreach ($groupedStep as $groupId => $step) {
			$total = $step->count();
			$status =  $this->checkStepStatus($step);
			$statistics[$groupId] = [
				'total' => $total,
				'completed' =>  $status['completed'] ?? 0,
				'pending' => $status['pending'] ?? 0,
				'extra' => $this->getExtraStatistics($step),
			];
		}
		return collect($statistics);
	}

	private function checkStepStatus($steps)
	{
		return $steps->countBy(function ($step) {
			if (empty($step->deleted_at)) return 'pending';

			$isExpired = true;
			$isDelay = $step->step['type'] === 'delay';
			if ($isDelay) {
				$properties = $step->step['properties'];
				$duration = $properties['duration'];
				$unit = $properties['unit'];
				$expiredDate = Carbon::parse($step->execute_at);
				if ($unit === 'minute') {
					$expiredDate->addMinutes($duration);
				} else if ($unit === 'hour') {
					$expiredDate->addHours($duration);
				} else if ($unit === 'day') {
					$expiredDate->addDays($duration);
				}
				$isExpired = Carbon::now() >= $expiredDate;
			}
			return (!$isDelay || $isExpired) ? 'completed' : 'pending';
		});
	}

	private function getExtraStatistics($steps)
	{
		$statistics = null;
		$steps->each(function ($step) use (&$statistics) {
			if (!isset($step->step)) return;

			if ($step->step['kind'] === 'automationSendEmailAction') {
				$statistics = EmailReport::firstWhere('email_id', $step->step['properties']['email_id']);
			}
		});
		return $statistics;
	}

	/**
	 * Get triggered step according to specification, by default return all
	 *  
	 * @param string status status of automation [draft, activated, paused]
	 *
	 * @return TriggeredStep
	 * @throws RuntimeException 
	 */
	public function getTriggeredStepByStatus(string|array $status = null)
	{
		if (gettype($status) === 'string') $status = empty($status) ? [] : [$status];

		if (count($status) === 0) return TriggeredStep::withTrashed();

		if (count(array_diff($status, ['draft', 'activated', 'paused']))) {
			throw new RuntimeException('Invalid status provided');
		}

		$activatedStatusId = AutomationStatus::firstWhere('name', $status)->id;
		return TriggeredStep::whereHas('automation', function ($query) use ($activatedStatusId) {
			$query->where('automation_status_id', $activatedStatusId);
		})->withTrashed();
	}

	public function getTotalEntered()
	{
		return $this->triggeredStepGroupByBatch->count();
	}

	public function getTotalPending()
	{
		return $this->triggeredStepProgressGroupByBatch->where('pending', '>', 0)->count();
	}

	public function getTotalCompleted()
	{
		return $this->triggeredStepProgressGroupByBatch->where('pending', '=', 0)->count();
	}
}
