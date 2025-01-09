<?php

namespace App\Traits;

use App\AffiliateMemberAccount;
use App\AffiliateMemberCampaign;
use App\AffiliateMemberCommission;
use App\AffiliateMemberConditionGroupLevel;
use App\AffiliateMemberLifetimeCommission;
use App\AffiliateMemberParticipant;
use App\AffiliateMemberSetting;
use App\Models\Promotion\Promotion;
use App\Order;
use Cookie;
use App\Repository\Checkout\CheckoutData;
use App\Services\AffiliateCookieService;
use Illuminate\Support\Facades\DB;
use Mail;

trait AffiliateMemberCommissionTrait
{
    /**
     * @param \App\Order $order
     * @param string $domain
     */
    public function calculateAffiliateMemberCommissions(Order $order, string $domain, $isOneClickUpsell = false): void
    {
        $order->refresh();
        $promotions = CheckoutData::$promotion ?? null;
        parse_str(AffiliateCookieService::getReferToken(), $referral);
        foreach ($promotions?->filter(function ($el) {
            return $el->promotion_method !== 'automatic';
        })->values() as $promo) {
            $pivot = DB::table('affiliate_member_participant_promotion')->where(['promotion_id' => $promo->id])?->first();
            if (!$pivot) continue;
            $participant = AffiliateMemberParticipant::find($pivot?->affiliate_member_participant_id);
            if (!$participant) continue;
            $referral['ref'] = $participant?->member?->referral_identifier;
            break;
        }
        $accountId = $order->account_id;
        $settings = AffiliateMemberSetting::findOneOrCreateDefault($accountId);
        $parsedOrigin = parse_url($_SERVER['HTTP_ORIGIN']);
        $domain = $parsedOrigin['host'];
        $campaigns = AffiliateMemberCampaign
            ::with('conditionGroups', 'domain')
            ->where('account_id', $accountId)
            ->whereHas('domain', static function ($query) use ($domain) {
                $query->where('domain', $domain);
            })
            ->orderByDesc('id')
            ->get();

        \Log::debug('campaigns', [
            'c' => $campaigns
        ]);

        if ($campaigns->count() === 0) {
            return;
        }

        $member = AffiliateMemberAccount
            ::where([
                'account_id' => $accountId,
                'referral_identifier' => ($referral['ref'])
            ])
            ->first();

        if (!$member) {
            \Log::error('Affiliate member account is missing.', [
                'order_id' => $order->id,
                'account_id' => $accountId,
                'member' => $member
            ]);
            return;
        }

        $participant = $this->getParticipant($member, $order);

        if (!$participant) {
            \Log::error('Affiliate participant is missing.', [
                'order_id' => $order->id,
                'account_id' => $accountId,
                'member' => $member
            ]);
            return;
        }
        if ($participant->status !== 'approved') return;
        if (!$isOneClickUpsell) {
            $orderDetails = $order->orderDetails;
        } else {
            $orderDetails = $order->orderDetails->count() > 1 ? collect([$order->orderDetails->last()]) : collect([]);
        }
        $matchedCampaigns = $orderDetails->map(static function ($orderDetail) use ($campaigns) {
            $matchedCampaign = null;

            foreach ($campaigns as $campaign) {
                //  default to match all products
                if (!$campaign->hasProducts() && !$campaign->hasCategories()) {
                    $matchedCampaign = $campaign;
                    break;
                }

                $ids = $campaign->hasProducts()
                    ? collect($orderDetail->usersProduct->id)
                    : $orderDetail->usersProduct->categories->pluck('id');

                if ($campaign->productOrCategoryIds->intersect($ids)->count() !== 0) {
                    $matchedCampaign = $campaign;
                    break;
                }
            }

            return $matchedCampaign;
        });

        \Log::debug('matched campaigns', [
            'cc' => $matchedCampaigns
        ]);

        $matchedCampaigns = $matchedCampaigns->map(static function ($campaign) use ($participant) {
            if ($campaign)
                $campaign->matchedConditionGroup = $campaign
                    ->conditionGroups
                    ->first(static function ($conditionGroup) use ($participant) {
                        $memberGroups = $conditionGroup->groups;

                        // no group specified, this condition group matches all participants
                        if ($memberGroups->count() === 0) {
                            return true;
                        }

                        return $memberGroups
                            ->pluck('participants.*.id')
                            ->flatten()
                            ->contains($participant->id);
                    });

            return $campaign;
        });

        \Log::debug('matched campaign w/ matched cond group', [
            'mcwmcg' => $matchedCampaigns
        ]);

        // TODO: pending for in-depth testing
        $batchCommissions = [];
        foreach ($orderDetails as $idx => $orderDetail) {
            $matchedConditionGroup =
                $matchedCampaigns[$idx] ?
                $matchedCampaigns[$idx]->matchedConditionGroup :
                null;

            // skip if no group matched participant in that particular campaign
            if (is_null($matchedConditionGroup)) {
                continue;
            }

            $levels = AffiliateMemberConditionGroupLevel
                ::with('conditionGroup')
                ->where(
                    'affiliate_member_condition_group_id',
                    $matchedConditionGroup->id
                )
                ->orderBy('level')
                ->get();

            $currentParticipant = $participant; // parent default to self initially
            foreach ($levels as $level) {
                $levelCount = $level->level;

                // NULL parent_id is root, which is excluded from commission distribution
                if (!$currentParticipant->parent_id) {
                    break;
                }

                $commissionIsPercentage = $level->commission_type === 'percentage';
                $commissionRate = $level->commission_rate;

                $commission = $commissionIsPercentage
                    ? ($commissionRate * $orderDetail->total / 100)
                    : $commissionRate * $orderDetail->quantity;
                $batchCommissions[$levelCount] = array_merge(
                    $batchCommissions[$levelCount] ?? [],
                    [
                        'account_id' => $accountId,
                        'affiliate_member_campaign_id' => $level->conditionGroup->campaign->id,
                        'affiliate_member_participant_id' => $currentParticipant->id,
                        'order_id' => $orderDetail->order->id,
                        'order_detail_id' => $orderDetail->id,
                        'status' => $settings->auto_approve_commission ? 'approved' : 'pending',
                        'level' => $levelCount,
                        'currency' => $order->currency,
                        'commission' => $commission + ($batchCommissions[$levelCount]['commission'] ?? 0),
                        'affiliate_email' => $currentParticipant->member->email,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );

                // remember to set participant to new parent after each iteration
                $currentParticipant = $currentParticipant->parent;
            }
        }

        \Log::debug('batch comm', [
            'bc' => $batchCommissions
        ]);

        AffiliateMemberCommission::insert($batchCommissions);
        $this->runNotificationEmailProcess($batchCommissions, $participant);
    }

    /**
     * Get participant depends on the following situations:
     * -----------------------------------------------------
     * - TL;DR precedence:
     *   -> lifetime commission
     *   -> promotion
     *   -> referral link
     *
     * -----------------------------------------------------
     * - If lifetime commission is not enabled
     *   - If promo code present, the corresponding affiliate will be selected
     *   - Else, default participant will be selected based on ref
     *
     * - If lifetime commission is enabled
     *   - lifetime commission db entry will be fetched if found matched with
     *     the order contact's id, account id and participant id. Else, create a new
     *     entry binding affiliate member participant with contact.
     *   - This simply means that a corresponding member (and its parents) will be credited
     *     for every purchases/orders the contact made, regardless of ref link
     *
     * @param \App\AffiliateMemberAccount $member
     * @param \App\Order $order
     * @return \App\AffiliateMemberParticipant|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|mixed|object
     */
    private function getParticipant(AffiliateMemberAccount $member, Order $order)
    {
        $accountId = $order->account_id;
        $settings = AffiliateMemberSetting::findOneOrCreateDefault($accountId);
        $lifetimeCommissionEnabled = (bool)$settings->enable_lifetime_commission;
        $defaultParticipant = $member->participant;

        if ($lifetimeCommissionEnabled) {
            $lifetimeCommissionEntry = AffiliateMemberLifetimeCommission::firstOrCreate(
                [
                    'account_id' => $accountId,
                    'processed_contact_id' => $order->processed_contact_id,
                ],
                [
                    'affiliate_member_participant_id' => $defaultParticipant->id,
                ]
            );

            return $lifetimeCommissionEntry->participant;
        }

        $sessionPromo = session('appliedPromotion') ?? [];
        $promotion = Promotion::find(array_keys($sessionPromo)[0] ?? null);

        // Note the with and whereHas, they are written for ONE promo per order.
        // If order can use multiple promo later then you probably need to change these
        $promoParticipant = AffiliateMemberParticipant
            ::with(['promotions' => static function ($q) use ($promotion) {
                $q->where('id', $promotion->id ?? null);
            }])
            ->where('account_id', $accountId)
            ->whereHas('promotions', static function ($q) use ($promotion) {
                $q->where('id', $promotion->id ?? null);
            })
            ->first();

        return $promoParticipant ?? $defaultParticipant;
    }

    private function runNotificationEmailProcess($batchCommissions, $participant)
    {
        $affiliateAccount = $participant?->member ?? null;
        // $parent = $participant?->ancestorsOf($participant->id)?->last() ?? null;
        foreach ($batchCommissions as $batchCommission) {
            $commission = collect($batchCommission);
            \Log::debug('commission_status', [
                'status' => $commission['status'],
            ]);
            if ($commission['status'] === 'approved') {
                Mail::to($affiliateAccount?->email)->send(new \App\Mail\AffiliateMemberCommissionEmail($affiliateAccount, $commission));
            }
            // if($parent){
            //     $member = AffiliateMemberAccount::find($parent->id) ?? null;
            //     Mail::to($affiliateAccount?->email)->send(new \App\Mail\AffiliateMemberConversionEmail($member));
            // }
        }
    }
}
