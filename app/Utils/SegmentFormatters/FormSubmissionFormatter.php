<?php

namespace App\Utils\SegmentFormatters;

use App\LandingPageForm;

class FormSubmissionFormatter extends BaseFormatter implements IFormatter
{
	/**
	 * @inheritDoc
	 */
	public function format(array $subCondition): string
	{
		if ($this->isSubConditionKeyTimeFrame($subCondition)) {
			return $this->formatSubConditionTimeFrame($subCondition);
		}

		if ($this->isSubConditionKeyDuration($subCondition)) {
			return $this->formatSubConditionDuration($subCondition);
		}

		$formId = $subCondition['value'];
		$formTitle = LandingPageForm::find($formId)?->title;

		return 'of ' . $formTitle . ' ';
	}
}
