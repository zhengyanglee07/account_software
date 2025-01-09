<?php

namespace App\Services\PeopleFilters;

use App\LandingPageFormContent;
use App\Traits\AuthAccountTrait;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FormSubmissionFilter extends BaseFilter implements IFilter
{
	use AuthAccountTrait;

	/**
	 * @inheritDoc
	 */
	public function filter(Collection $contacts, $subConditions): Collection
	{
		$accountId = $this->getCurrentAccountId();
		$formId = $subConditions[self::FORM_SUBMISSION]['value'];
		$formSubmissionTimeframe = $subConditions[self::FORM_SUBMISSION_TIMEFRAME]['key'];
		$formSubmissionDuration = $subConditions[self::FORM_SUBMISSION_DURATION]['value'];
		$formSubmissionDurationPattern = $subConditions[self::FORM_SUBMISSION_DURATION]['key'];

		$query = LandingPageFormContent::with('visitor:id,processed_contact_id')->where([
			'account_id' => $accountId,
			'landing_page_form_id' => $formId,
		])->select('id', 'created_at', 'visitor_id');

		switch ($formSubmissionTimeframe) {
			case 'in the last':
				$startDate = Carbon::now()->sub($formSubmissionDuration, $formSubmissionDurationPattern);
				$submissions = $query->whereDate('created_at', '>=', $startDate);
				break;
			case 'between':
				$startDate = Carbon::parse($formSubmissionDuration['from']);
				$endDate = Carbon::parse($formSubmissionDuration['to']);
				$submissions = $query->whereBetween('created_at', [$startDate, $endDate]);
				break;
			default:
				$submissions = $query;
		}
		
		$contactIds = $submissions->get()->pluck('processed_contact_id')->unique()->toArray();

		return $contacts->filter(static function ($contact) use ($contactIds) {
			return in_array($contact->id, $contactIds);
		});
	}
}
