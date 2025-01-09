<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountDomain;
use App\AffiliateMemberAccount;
use App\AffiliateMemberParticipant;
use App\AffiliateMemberCampaign;
use App\AffiliateMemberCommission;
use App\AffiliateMemberSetting;
use App\AffiliateMemberReferralClickLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAffiliateMemberPasswordRequest;
use App\Traits\CurrencyConversionTraits;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use App\Traits\PublishedPageTrait;

class AffiliateMemberDashboardController extends Controller
{
    use CurrencyConversionTraits, PublishedPageTrait;

    public function getBaseData(Request $request)
    {
        return response()->json([
            'memberName' => $request->user()->name(),
            'account' => Account::find($this->getCurrentAccountId()),
        ]);
    }

    public function getDashboardData(Request $request)
    {
        /**
         * @var \App\AffiliateMemberAccount
         */
        $member = $request->user();
        $participant = $member->participant;
        $clicks = AffiliateMemberReferralClickLog::where('referral_identifier', $member->referral_identifier)->count();
        $defaultCurrency = $this->getDefaultCurrency();
        $promoCodes = $participant->promotions->pluck('discount_code');
        $commissions = [
            'totalCommissions' => 0,
            'pendingCommissions' => 0,
            'approvedCommissions' => 0,
            'withdrawnCommissions' => 0,
            'disapprovedCommissions' => 0, // disapproved commission = disapproved payout
        ];

        foreach ($participant->commissions as $commission) {
            $commissionAmount = $this->convertCurrency(
                $commission->commission,
                $commission->currency,
                true,
                true
            );

            $commissions['totalCommissions'] += $commissionAmount;
            $commissions["{$commission->status}Commissions"] += $commissionAmount;
        }

        $disapprovedPayouts = 0;
        foreach ($participant->payouts as $payout) {
            $payoutAmount = $payout->amount;
            $payoutStatus = $payout->status;

            if ($payoutStatus === 'paid') {
                $commissions['approvedCommissions'] -= $payoutAmount;
                $commissions['withdrawnCommissions'] += $payoutAmount;
            }

            if ($payoutStatus === 'disapproved') {
                $commissions['approvedCommissions'] -= $payoutAmount;
                $disapprovedPayouts += $payoutAmount;
            }
        }
        $levels = $participant->getSublines();
        return response()->json(
            [
                'clicks' => $clicks,
                'participant' => $participant,
                'defaultCurrency' => $defaultCurrency,
                'promoCodes' => $promoCodes,
                'disapprovedPayouts' => $disapprovedPayouts,
                'totalCommissions' => $commissions['totalCommissions'],
                'pendingCommissions' => $commissions['pendingCommissions'],
                'approvedCommissions' => $commissions['approvedCommissions'],
                'disapprovedCommissions' => $commissions['disapprovedCommissions'],
                'withdrawnCommissions' => $commissions['withdrawnCommissions'],
                'levels' => $levels,
            ]
        );
    }

    /**
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCampaignsData(Request $request)
    {
        $member = $request->user();
        $accountId = $member->account_id;
        $participant = $member->participant;
        $campaigns = AffiliateMemberCampaign
            ::with('conditionGroups', 'domain')
            ->where('account_id', $accountId)
            ->get()
            ->map(static function ($c) use ($member) {
                $domain = $c->domain->domain;
                $identifier = $member->referral_identifier;

                $c->refLink = "https://$domain?ref=$identifier";
                return $c;
            });
        $campaigns = $campaigns->map(function ($campaign) use ($participant) {
            foreach ($campaign->conditionGroups as $condition) {
                if ($condition->groups->count() > 0) {
                    $specificGroups = $condition->groups->map(function ($group) {
                        return $group->id;
                    });
                    foreach ($participant->groups->map(function ($group) {
                        return $group->id;
                    }) as $participantGroup) {
                        if ($specificGroups->contains($participantGroup)) {
                            $campaign->levels = $condition?->levels;
                            return $campaign;
                        }
                    }
                } else {
                    $campaign->levels = $condition?->levels;
                    return $campaign;
                }
            }
        });
        $campaigns =  $participant->status === 'approved' ? $campaigns->filter(function ($c) {
            return $c;
        })->values() : [];
        return response()->json(['member' => $member, 'campaigns' => $campaigns]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getConversionsData(Request $request)
    {
        $member = $request->user();
        $account = Account::find($member->account_id);
        $tz = $account->timeZone;
        $defaultCurrency = $this->getDefaultCurrency();

        $commissions = AffiliateMemberCommission
            ::with('participant', 'order', 'campaign')
            ->where('affiliate_member_participant_id', $member->participant->id)
            ->get()
            ->map(function ($c) use ($tz) {
                $c->orderTotal = $this->convertCurrency(
                    $c->order->total,
                    $c->order->currency,
                    true,
                    true
                );
                $c->convertedCommission = $this->convertCurrency(
                    $c->commission,
                    $c->currency,
                    true,
                    true
                );
                $c->datetime = $c->created_at->timezone($tz)->isoFormat('MMMM D, YYYY \\at h:mm a');
                return $c;
            });
        if ($member->participant->status === 'pending') $commissions = [];
        return response()->json(['member' => $member, 'commissions' => $commissions, 'defaultCurrency' => $defaultCurrency]);
    }

    /**
     * Show commission payments/payouts of a member
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPayoutsData(Request $request)
    {
        $member = $request->user();
        $settings = AffiliateMemberSetting::findOneOrCreateDefault($member->account_id);
        $tz = Account::find($member->account_id)->timeZone;
        $payouts = $member->participant->payouts->map(function ($po) use ($tz) {
            $po->datetime = $po->created_at->timezone($tz)->isoFormat('MMMM D, YYYY \\at h:mm a');
            $po->convertedAmount = $this->convertCurrency(
                $po->amount,
                $po->currency,
                true,
                true
            );

            return $po;
        });

        $minimumPayout = (float)$settings->minimal_payout;
        $approvedCommissions = $member->participant->commissions->reduce(function ($carry, $c) {
            if ($c->status !== 'approved') {
                return $carry;
            }

            return $carry + $this->convertCurrency(
                $c->commission,
                $c->currency,
                true,
                true
            );
        }, 0);
        $defaultCurrency = $this->getDefaultCurrency();

        $totalPayouts = $payouts->reduce(static function ($carry, $payout) {
            return $carry + $payout->convertedAmount; // custom props from $payouts
        }, 0);

        // disapproved payouts are like disapproved commissions, so we will
        // just minus all sorts of payouts regardless of their statuses
        // (pending, paid, disapproved) for available commissions
        $availableCommissions = $approvedCommissions - $totalPayouts;

        // just in case payouts > available commissions
        // which is an extremely rare case imo, if no miscalculation
        $availableCommissions = $availableCommissions < 0 ? 0 : $availableCommissions;
        $publishPageBaseData = $this->getPublishedPageBaseData();
        if ($member->participant->status === 'pending') $payouts = [];
        return response()->json([
            'member' => $member,
            'payouts' => $payouts,
            'minimumPayout' => $minimumPayout,
            'availableCommissions' => $availableCommissions,
            'defaultCurrency' => $defaultCurrency
        ]);
    }

    public function getProfileData(Request $request)
    {
        return response()->json(['member' => $request->user()]);
    }

    public function updateProfile(Request $request)
    {
        $member = AffiliateMemberAccount::findOrFail($request->member_id);
        $member->update($request->member);

        return response()->noContent();
    }

    public function updatePassword(UpdateAffiliateMemberPasswordRequest $request)
    {
        $password = Hash::make($request->password);

        $member = $request->user();
        $member->password = $password;
        $member->save();

        return response()->json(['message' => 'Successfully updated password']);
    }

    /**
     * Request pending payout from member
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function requestPayout(Request $request): Response
    {
        $request->validate([
            'amount' => 'required|numeric'
        ]);

        $member = $request->user();

        $member->participant->payouts()->create([
            'account_id' => $member->account_id,
            'status' => 'pending',
            'currency' => $this->getDefaultCurrency(false),
            'amount' => $request->amount,
        ]);

        return response()->noContent();
    }

    public function getAffiliateDomain()
    {
        $accountId = $this->getAccountId();
        $domain = AccountDomain
            ::where([
                'account_id' => $accountId,
                'is_affiliate_member_dashboard_domain' => 1
            ])
            ->first()
            ->domain ?? '';

        return response()->json(compact('domain'));
    }

    private function getDefaultCurrency($prefix = true)
    {
        $c = $this->getCurrencyArray(null);
        $fullCurrency = $c->currency;

        if (!$prefix) {
            return $fullCurrency;
        }

        return $c->prefix ?? $fullCurrency;
    }

    /**
     * Override getAccountId in trait
     *
     * @return mixed
     */
    private function getAccountId()
    {
        $domain = AccountDomain::whereDomain($_SERVER['HTTP_HOST'])->first();

        return $domain != null
            ? $domain->account_id
            : Auth::user()->account_id;
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard(): \Illuminate\Contracts\Auth\StatefulGuard
    {
        return Auth::guard('affiliateMember');
    }
}
