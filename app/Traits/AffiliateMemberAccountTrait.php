<?php

namespace App\Traits;

use App\AffiliateMemberAccount;
use App\AffiliateMemberCommission;
use App\ProcessedContact;

trait AffiliateMemberAccountTrait
{
    private function getReferralIdentifier($firstName, $lastName): string
    {
        $duplicatedNameCount = AffiliateMemberAccount
            ::where([
                'first_name' => $firstName,
                'last_name' => $lastName
            ])
            ->count();

        return urlencode("$firstName $lastName" . ($duplicatedNameCount === 0 ? '' : $duplicatedNameCount));
    }

    public function getAffiliateCommisionInfo($orderId){
        return AffiliateMemberCommission::where('order_id', $orderId)->get()->map(function($el){
            $el->campaign= $el?->campaign;
            $el->affRefKey = $el?->participant?->member?->referral_identifier ?? null;
            return $el;
        });
    }
}
