<?php

namespace App\Traits;

use App\AccountDomain;
use App\Page;

trait SalesChannelTrait
{
    use AuthAccountTrait;

    public $salesChannelMap = [
        'online-store' => 'Online Store',
        'mini-store' => 'Mini Store',
        'funnel' => 'Funnel',
    ];

    public function getCurrentSalesChannel($isDisplay = false)
    {
        $path = request()->path() ?? '';
        $isFunnel = Page::where(['account_id' => $this->getCurrentAccountId(), 'path' => $path])->whereNotNull('funnel_id')->exists();

        $salesChannel = $isFunnel ? 'funnel' : optional(AccountDomain::getDomainRecord())->type;

        if ($isDisplay) $salesChannel = $this->salesChannelMap[$salesChannel];
        return $salesChannel;
    }

    /**
     * Check is matched to current sales channel
     *
     * @param  string $salesChannel [online-store | mini-store | funnel]
     * @return boolean
     */
    public function checkIsCurrentSalesChannel($salesChannel)
    {
        return $salesChannel === $this->getCurrentSalesChannel();
    }
}
