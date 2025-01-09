<?php

namespace App\Services\PeopleFilters;

use App\EcommerceVisitor;
use App\funnel;
use App\Traits\AuthAccountTrait;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class SiteActivityFilter extends BaseFilter implements IFilter
{
	use AuthAccountTrait;
	
	/**
	 * @var array
	 */
	private $subConditions;

	/**
	 * @var Collection
	 */
	private $contacts;

	/**
	 * @inheritDoc
	 */
	public function filter(Collection $contacts, $subConditions): Collection
	{
		$this->subConditions = $subConditions;
		$this->contacts = $contacts;

		$salesChannel = $this->subConditions[self::VISIT_SALES_CHANNEL]['key'];
		$funnelId = $this->subConditions[self::VISIT_SALES_CHANNEL]['value'];

		$visitorQuery = EcommerceVisitor::where([
			'account_id' => $this->getCurrentAccountId(),
			'sales_channel' => $salesChannel,
		])
		->whereNotNull('processed_contact_id')
		->when($salesChannel === 'funnel', function ($query) use ($funnelId) {
			$query->where('funnel_id', $funnelId);
		});

		$visitTimeFrame = $this->subConditions[self::VISIT_TIMEFRAME]['key'];
		$visitDuration = $this->subConditions[self::VISIT_DURATION]['value'];
		$visitDurationPattern = $this->subConditions[self::VISIT_DURATION]['key'];

		switch($visitTimeFrame) {
			case 'in the last':
				$startDate = Carbon::now()->sub($visitDuration, $visitDurationPattern);
				$visitors = $visitorQuery->whereDate('created_at', '>=', $startDate);
				break;
			case 'between':
				$startDate = Carbon::parse($visitDuration['from']);
				$endDate = Carbon::parse($visitDuration['to']);
				$visitors = $visitorQuery->whereBetween('created_at', [$startDate, $endDate]);
				break;
			default:
				$visitors = $visitorQuery;
		}

		$landingpageIds = [];
		//* cater for builder ecommerce page
		$pageType = $this->subConditions[self::VISIT_PAGE]['key'];
		$pageId = $this->subConditions[self::VISIT_PAGE]['value'];
		$visitFrequencyPattern = $this->subConditions[self::VISIT_FREQUENCY_PATTERN]['key'];
		$visitFrequencyInTimes = (int)$this->subConditions[self::VISIT_FREQUENCY_PATTERN]['value'];

		$comparisonOperator = match ($visitFrequencyPattern) {
			'at least' => '>=',
			'at most' => '<=',
			'exactly' => '=',
		};

		if($salesChannel === 'funnel') {
			$landingpageIds = funnel::find($funnelId)->landingpages()->pluck('id');
		}

		$visitors = $visitors->whereHas('activityLogs', function(Builder $query) use($salesChannel, $pageType, $pageId, $landingpageIds) {
			$query = $query->where('type', $pageType);
			if($pageId === 'any') {
				return $salesChannel === 'funnel'
					? $query->whereIn('value', $landingpageIds)
					: $query;
			}
			return $query->where('value', $pageId);
		}, $comparisonOperator, $visitFrequencyInTimes)->pluck('processed_contact_id');

		$visitCondition = $this->subConditions[self::VISIT_CONDITION]['key'];

		if($visitCondition === 'has visited a page') {
			return $this->contacts->whereIn('id', $visitors);
		}
		return $this->contacts->whereNotIn('id', $visitors);
	}
}
