<?php

namespace App\Traits;

use App\Traits\AuthAccountTrait;
use App\LegalPolicy;
use Illuminate\Database\Eloquent\Collection;

trait LegalPolicyTrait
{
	use AuthAccountTrait;

	public function getLegalPolicy($columns = ['*']): Collection
	{
		return LegalPolicy::with('legalPolicyType')
			->where('account_id', $this->getCurrentAccountId())
			->whereNotNull('template')
			->orderBy('type_id', 'asc')
			->get($columns);
	}

	public function getAvailableLegalPolicy(): array
	{
		$legalPolicies  = [];
		LegalPolicy::with('legalPolicyType')
			->where('account_id', $this->getCurrentAccountId())
			->whereNotNull('template')
			->orderBy('type_id', 'asc')
			->each(function ($row) use (&$legalPolicies) {
				$pattern = '/<([^<\/>]*)([^<\/>]*)>([\s]*?|(?R))<\/\1>/imsU';
				$templateStr = preg_replace($pattern, '', $row->template);
				if (strlen($templateStr) > 0)
					$legalPolicies[] = [
						'name' => $row->legalPolicyType->name,
						'type' => $row->legalPolicyType->type,
					];
			});
		return $legalPolicies;
	}
}
