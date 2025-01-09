<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountDomain;
use App\AffiliateMemberAccount;
use App\AffiliateMemberCampaign;
use App\AffiliateMemberCommission;
use App\AffiliateMemberCommissionPayout;
use App\AffiliateMemberConditionGroup;
use App\AffiliateMemberConditionGroupLevel;
use App\AffiliateMemberGroup;
use App\AffiliateMemberParticipant;
use App\AffiliateMemberReferral;
use App\AffiliateMemberSetting;
use App\Category;
use App\Currency;
use App\EcommerceVisitor;
use App\Models\Promotion\Promotion;
use App\Services\RefKeyService;
use App\Traits\AffiliateMemberAccountTrait;
use App\Traits\AuthAccountTrait;
use App\Traits\CurrencyConversionTraits;
use App\UsersProduct;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\ProcessedContact;
use App\Order;
use App\AffiliateMemberReferralClickLog;
use Mail;

class AffiliateMemberController extends Controller
{
    use AuthAccountTrait, AffiliateMemberAccountTrait, CurrencyConversionTraits;

    /**
     * Get all affiliate members (participants) to show in All Affiliates page
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function allMembers()
    {
        $accountId = $this->getCurrentAccountId();
        $defaultCurrency = $this->getDefaultCurrency();
        $availablePromotions = Promotion
            ::where('account_id', $accountId)
            ->where('promotion_method', 'manual')
            ->where('promotion_status', 'active')
            ->whereDate('start_date', '<=', Carbon::now())
            ->orderBy('created_at', 'DESC')
            ->get();

        $participants = AffiliateMemberParticipant
            ::with('commissions', 'payouts', 'promotions')
            ->nonRoot($accountId)
            ->get()
            ->map(function ($participant) use ($defaultCurrency) {
                $commissions = $participant->commissions;

                $participant->total_commissions = $commissions
                    ->reduce(
                        function ($carry, $c) use ($defaultCurrency) {
                            return $carry + $this->convertCurrency(
                                $c->commission,
                                $c->currency ?? $defaultCurrency,
                                true,
                                true
                            );
                        },
                        0
                    );
                $participant->pending_payouts = $participant->payouts
                    ->where('status', 'pending')
                    ->reduce(
                        function ($carry, $c) use ($defaultCurrency) {
                            return $carry + $this->convertCurrency(
                                $c->amount,
                                $c->currency ?? $defaultCurrency,
                                true,
                                true
                            );
                        },
                        0
                    );

                $participant->order_counts = $commissions->count();
                $participant->total_sales = $commissions->reduce(
                    function ($carry, $c) use ($defaultCurrency) {
                        return $carry + $this->convertCurrency(
                            $c->order->total,
                            $c->currency ?? $defaultCurrency,
                            true,
                            true
                        );
                    },
                    0
                );

                $participant->discount_codes = $participant
                    ->promotions
                    ->pluck('discount_code') ?? collect();

                return $participant;
            });

        $groups = AffiliateMemberGroup::where('account_id', $accountId)->get();

        return Inertia::render('affiliate/pages/AllAffiliates', compact(
            'participants',
            'defaultCurrency',
            'availablePromotions',
            'groups'
        ));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function allCommissions()
    {
        $account = Account::find($this->getCurrentAccountId());
        $tz = $account->timeZone;
        $defaultCurrency = $this->getDefaultCurrency();

        $commissions = AffiliateMemberCommission
            ::with('campaign', 'participant', 'order')
            ->where('account_id', $account->id)
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

        return Inertia::render('affiliate/pages/Commissions', compact('commissions', 'defaultCurrency'));
    }

    /**
     * A page for hypershapes user to settle affiliate member commission payouts
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function allPayouts()
    {
        $defaultCurrency = $this->getDefaultCurrency();

        $payouts = AffiliateMemberCommissionPayout
            ::with('participant')
            ->where('account_id', $this->getCurrentAccountId())
            ->get()
            ->map(function ($po) {
                $po->convertedAmount = $this->convertCurrency(
                    $po->amount,
                    $po->currency,
                    true,
                    true
                );
                return $po;
            });

        return Inertia::render('affiliate/pages/Payouts', compact(
            'defaultCurrency',
            'payouts'
        ));
    }

    public function allGroups()
    {
        $groups = AffiliateMemberGroup
            ::with('participants')
            ->where('account_id', $this->getCurrentAccountId())
            ->get();

        return Inertia::render('affiliate/pages/Groups', compact('groups'));
    }

    /**
     * Create new aff mem group
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createGroup(Request $request): JsonResponse
    {
        $accountId = $this->getCurrentAccountId();

        $request->validate([
            'title' => 'required|unique:affiliate_member_groups,title,NULL,id,account_id,' . $accountId
        ]);

        $group = AffiliateMemberGroup::create([
            'account_id' => $accountId,
            'title' => $request->title
        ]);

        return response()->json(compact('group'));
    }

    /**
     * Update aff mem group
     *
     * @param \App\AffiliateMemberGroup $group
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateGroup(AffiliateMemberGroup $group, Request $request): Response
    {
        $request->validate([
            'title' => 'required|unique:affiliate_member_groups,title,' . $group->id . ',id,account_id,' . $group->account_id
        ]);

        $group->update(['title' => $request->title]);

        return response()->noContent();
    }

    public function destroyMemberGroup(AffiliateMemberGroup $group)
    {
        $group->delete();
        return response()->noContent();
    }

    /**
     * Update affiliate member program participant
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateParticipant(Request $request): Response
    {
        $request->validate([
            'participantId' => 'required|int',
        ]);

        $participant = AffiliateMemberParticipant::with('promotions')->find($request->participantId);

        if (!$participant) {
            abort(404, 'Participant not found');
        }

        $initialStatus = $participant->status;
        $participant->update($request->all());
        if ($request->exists('status') && $request->status !== $initialStatus && $request->status === 'approved') {
            $user =  $participant->member;
            Mail::to($user->email)->send(new \App\Mail\AffiliateMemberWelcomeEmail($user));
            $parent = $participant->ancestorsOf($participant->id)?->last() ?? null;
            if ($parent) $parent->sendReferEmail();
        }

        // remove promo codes if status is being updated
        // and new status isn't 'approved'
        if ($request->exists('status') && $request->status !== 'approved') {
            $participant->promotions()->detach();
        }

        return response()->noContent();
    }

    /**
     * Assign(sync) promotions (discount code) with affiliate member program participant
     * ------------------------------------------------------------------------
     *
     * Note: you may want to remove/amend (promotionId === 'none') condition block
     *       if multiple promotions per participant is unlocked, since this
     *       deletes all promotions associated to a participant
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function assignPromoCode(Request $request): Response
    {
        $promotionId = $request->promotionId;
        $participant = AffiliateMemberParticipant::find($request->participantId);

        if ($promotionId === 'none') {
            $participant->promotions()->detach();
            return response()->noContent();
        }

        $participant->promotions()->sync([$promotionId]);

        return response()->noContent();
    }

    /**
     * Update commission
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateCommission(Request $request): Response
    {
        $request->validate([
            'commissionId' => 'required|int',
        ]);

        $commission = AffiliateMemberCommission::findOrFail($request->commissionId);
        $initialStatus = $commission->status;
        $commission->update($request->all());
        if ($request->exists('status') && $request->status !== $initialStatus && $request->status === 'approved') {
            $user =  $commission->participant->member;
            Mail::to($user?->email)->send(new \App\Mail\AffiliateMemberCommissionEmail($user, $commission));
        }
        return response()->noContent();
    }

    /**
     * Update commission
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function bulkUpdateCommissions(Request $request): Response
    {
        $request->validate([
            'ids' => 'required|array',
        ]);
        foreach ($request->ids as $id) {
            $commission = AffiliateMemberCommission::findOrFail($id);
            $commission->update($request->all());
        }

        return response()->noContent();
    }

    /**
     * Update affiliate member commission payout
     *
     * @param \App\AffiliateMemberCommissionPayout $payout
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCommissionPayout(AffiliateMemberCommissionPayout $payout, Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:paid,disapproved,pending'
        ]);

        $initialStatus = $payout->status;

        $status = $request->status;
        $payout->update([
            'status' => $status,
            'paid_at' => $status === 'paid' ? now() : null
        ]);

        if ($request->exists('status') && $request->status !== $initialStatus && $request->status === 'paid') {
            $user =  $payout->participant->member;
            Mail::to($user?->email)->send(new \App\Mail\AffiliateMemberPayoutEmail($user, $payout));
        }

        return response()->json(compact('payout'));
    }

    /**
     * Add (sync) groups to selected participants
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function addGroupsToParticipants(Request $request): Response
    {
        $request->validate([
            'participantIds' => 'required|array',
            'groupIds' => 'required|array'
        ]);

        $participants = AffiliateMemberParticipant::whereIn('id', $request->participantIds)->get();

        foreach ($participants as $participant) {
            $participant->groups()->syncWithoutDetaching($request->groupIds);
        }

        return response()->noContent();
    }

    /**
     * Remove (detach) groups from selected participants
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function removeGroupsFromParticipants(Request $request): Response
    {
        $request->validate([
            'participantIds' => 'required|array',
            'groupIds' => 'required|array'
        ]);

        $participants = AffiliateMemberParticipant::whereIn('id', $request->participantIds)->get();

        foreach ($participants as $participant) {
            $participant->groups()->detach($request->groupIds);
        }

        return response()->noContent();
    }


    /**
     * Update/track each new unique visitor that clicks into affiliate member referral link
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateUniqueVisitor(Request $request): Response
    {
        $request->validate([
            'visitorRefKey' => 'required|int',
            'ref' => 'required'
        ]);

        $visitorRefKey = $request->visitorRefKey;
        $ref = urlencode($request->ref);
        $accountId = $this->getCurrentAccountId();
        $member = AffiliateMemberAccount::where('referral_identifier', $ref)->first();

        if (!$member) {
            \Log::error('Member with ref ' . $ref . ' could not be found', [
                'visitor_ref_key' => $visitorRefKey,
            ]);
            abort('404', 'Member not found');
        }

        $participant = AffiliateMemberParticipant
            ::where([
                'account_id' => $accountId,
                'affiliate_member_account_id' => $member->id
            ])
            ->firstOrFail();

        $visitor = EcommerceVisitor
            ::where('reference_key', $visitorRefKey)
            ->firstOrFail();

        AffiliateMemberReferral::firstOrCreate([
            'affiliate_member_participant_id' => $participant->id,
            'ecommerce_visitor_id' => $visitor->id
        ]);

        return response()->noContent();
    }

    /**
     * Get all verified domains in user account
     *
     * Note: now allows every verified domain
     *
     * @return \App\AccountDomain[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    private function getVerifiedDomains()
    {
        return AccountDomain
            ::where([
                'account_id' => $this->getCurrentAccountId(),
                'is_verified' => 1,
                //                'is_affiliate_member_dashboard_domain' => 0
            ])->get();
    }

    private function getDefaultCurrency()
    {
        $c = $this->getCurrencyArray(null);
        return $c->prefix ?? $c->currency;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function allCampaigns()
    {
        $accountId = $this->getCurrentAccountId();
        $campaigns = AffiliateMemberCampaign
            ::with('conditionGroups')
            ->where('account_id', $accountId)
            ->get();
        $dashboardDomain = AccountDomain
            ::where([
                'account_id' => $accountId,
                'is_affiliate_member_dashboard_domain' => 1
            ])
            ->first()
            ->domain ?? '';

        return Inertia::render('affiliate/pages/Campaigns', compact(
            'campaigns',
            'dashboardDomain'
        ));
    }

    /**
     * Show edit affiliate campaign page
     *
     * @param $reference_key
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editAffiliateMemberCampaign($reference_key)
    {
        $accountId = $this->getCurrentAccountId();
        $campaign = AffiliateMemberCampaign
            ::with('domain', 'conditionGroups', 'categories', 'products')
            ->where([
                'account_id' => $accountId,
                'reference_key' => $reference_key
            ])
            ->firstOrFail();
        $domains = $this->getVerifiedDomains();
        $defaultCurrency = $this->getDefaultCurrency();

        return Inertia::render('affiliate/pages/CampaignSetup', compact(
            'campaign',
            'domains',
            'defaultCurrency'
        ));
    }

    /**
     * Store new campaign. Create new settings and dummy parent participant if not exists
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\RefKeyService $refKeyService
     * @throws \Throwable
     */
    public function storeAffiliateMemberCampaign(Request $request, RefKeyService $refKeyService)
    {
        $request->validate([
            'title' => 'required|string',
            'domain' => 'required|string',
            'type' => 'required|in:all,product,category',
            'productsOrCategories' => 'required_if:type,product,category|array',
            'conditionGroups' => 'required|array',
            'conditionGroups.*.id' => 'required',
            'conditionGroups.*.groups' => 'present|array',
            'conditionGroups.*.groups.*.id' => 'required',
            'conditionGroups.*.groups.*.title' => 'required|string',
            'conditionGroups.*.levels' => 'required|array',
            'conditionGroups.*.levels.*.id' => 'required',
            'conditionGroups.*.levels.*.commission_rate' => 'required|numeric',
            'conditionGroups.*.levels.*.commission_type' => 'required|in:percentage,fixed',
        ]);

        $user = Auth::user();
        $accountId = $user->currentAccountId;
        $domain = AccountDomain
            ::where([
                'account_id' => $accountId,
                'domain' => $request->domain
            ])
            ->firstOrFail();

        // initialize settings if not exist during (first) campaign creation
        AffiliateMemberSetting::findOneOrCreateDefault($accountId);

        DB::beginTransaction();

        try {
            $title = $request->title;
            $campaign = AffiliateMemberCampaign::create([
                'reference_key' => $refKeyService->getRefKey(new AffiliateMemberCampaign),
                'account_id' => $accountId,
                'account_domain_id' => $domain->id,
                'title' => $title,
                'slug' => Str::slug($title),
            ]);

            // products/categories
            $type = $request->type;
            $productsOrCategoriesIds = collect($request->productsOrCategories)->pluck('id');

            if ($type === 'product') {
                $campaign->products()->sync($productsOrCategoriesIds);
            }

            if ($type === 'category') {
                $campaign->categories()->sync($productsOrCategoriesIds);
            }

            foreach ($request->conditionGroups as $cgIdx => $reqConditionGroup) {
                $savedGroup = AffiliateMemberConditionGroup::create([
                    'affiliate_member_campaign_id' => $campaign->id,
                    'priority' => $cgIdx + 1
                ]);

                // affiliate groups
                $affiliateGroupIds = collect($reqConditionGroup['groups'])->pluck('id');
                $savedGroup->groups()->sync($affiliateGroupIds);

                // levels
                foreach ($reqConditionGroup['levels'] as $idx => $levelInfo) {
                    $savedGroup->levels()->create([
                        'account_id' => $this->getCurrentAccountId(),
                        'level' => $idx + 1,
                        'commission_rate' => $levelInfo['commission_rate'],
                        'commission_type' => $levelInfo['commission_type']
                    ]);
                }
            }

            // dummy root parent
            AffiliateMemberParticipant::firstOrCreate(['account_id' => $accountId], []);

            DB::commit();
        } catch (\Throwable $th) {
            \Log::error('Storing affiliate member campaign', [
                'msg' => $th->getMessage(),
                'code' => $th->getCode(),
                'account_id' => $accountId,
                'request' => $request->all()
            ]);
            DB::rollBack();
            abort(422, $th->getMessage());
        }

        return response()->noContent();
    }

    /**
     * Update campaign
     *
     * @param $reference_key
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateAffiliateMemberCampaign($reference_key, Request $request): Response
    {
        $request->validate([
            'title' => 'required|string',
            'domain' => 'required|string',
            'type' => 'required|in:all,product,category',
            'productsOrCategories' => 'required_if:type,product,category|array',
            'conditionGroups' => 'required|array',
            'conditionGroups.*.id' => 'required',
            'conditionGroups.*.groups' => 'present|array',
            'conditionGroups.*.groups.*.id' => 'required',
            'conditionGroups.*.groups.*.title' => 'required|string',
            'conditionGroups.*.levels' => 'required|array',
            'conditionGroups.*.levels.*.id' => 'required',
            'conditionGroups.*.levels.*.commission_rate' => 'required|numeric',
            'conditionGroups.*.levels.*.commission_type' => 'required|in:percentage,fixed',
        ]);

        $accountId = $this->getCurrentAccountId();
        $domain = AccountDomain
            ::where([
                'account_id' => $accountId,
                'domain' => $request->domain
            ])
            ->firstOrFail();
        $campaign = AffiliateMemberCampaign
            ::where([
                'account_id' => $accountId,
                'reference_key' => $reference_key
            ])
            ->firstOrFail();

        DB::beginTransaction();

        try {
            $campaign->update(['title' => $request->title, 'account_domain_id' => $domain->id]);

            $type = $request->type;
            $productsOrCategoriesIds = collect($request->productsOrCategories)->pluck('id');

            if ($type === 'all') {
                $campaign->products()->sync([]);
                $campaign->categories()->sync([]);
            }

            if ($type === 'product') {
                $campaign->products()->sync($productsOrCategoriesIds);
                $campaign->categories()->sync([]);
            }

            if ($type === 'category') {
                $campaign->categories()->sync($productsOrCategoriesIds);
                $campaign->products()->sync([]);
            }

            $updatedConditionGroupIds = [];
            foreach ($request->conditionGroups as $cgIdx => $reqConditionGroup) {
                $newPriority = $cgIdx + 1;

                // =========================
                // settle condition groups
                // =========================
                $conditionGroup = AffiliateMemberConditionGroup::updateOrCreate(
                    [
                        'id' => $reqConditionGroup['id'],
                        'affiliate_member_campaign_id' => $campaign->id
                    ],
                    [
                        'priority' => $newPriority
                    ]
                );
                $updatedConditionGroupIds[] = $conditionGroup->id;

                // ============================================
                // settle affiliate groups in condition groups
                // ============================================
                $affiliateGroupIds = collect($reqConditionGroup['groups'])->pluck('id');
                $conditionGroup->groups()->sync($affiliateGroupIds);

                // =======================================
                // settle levels
                // =======================================
                $updatedLevelIds = [];
                foreach ($reqConditionGroup['levels'] as $levelIdx => $reqLevel) {
                    $level = AffiliateMemberConditionGroupLevel::updateOrCreate(
                        [
                            'id' => $reqLevel['id'],
                            'affiliate_member_condition_group_id' => $conditionGroup->id,
                            'account_id' => $accountId
                        ],
                        [
                            'level' => $levelIdx + 1,
                            'commission_rate' => $reqLevel['commission_rate'],
                            'commission_type' => $reqLevel['commission_type']
                        ]
                    );
                    $updatedLevelIds[] = $level->id;
                }

                $conditionGroup->levels()->whereNotIn('id', $updatedLevelIds)->delete();
            }

            $campaign->conditionGroups()->whereNotIn('id', $updatedConditionGroupIds)->delete();

            DB::commit();
        } catch (\Throwable $th) {
            \Log::error('Update affiliate member campaign', [
                'msg' => $th->getMessage(),
                'code' => $th->getCode(),
                'account_id' => $accountId,
                'request' => $request->all()
            ]);

            DB::rollBack();
            abort(500, 'Something wrong during affiliate campaign update');
        }

        return response()->noContent();
    }

    public function getConditionTypeValues()
    {
        $accountId = $this->getCurrentAccountId();
        $products = UsersProduct::where('account_id', $accountId)->get();
        $categories = Category::where('account_id', $accountId)->get();
        $groups = AffiliateMemberGroup::where('account_id', $accountId)->get();

        return response()->json(compact('products', 'categories', 'groups'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showCreateCampaignPage()
    {
        $domains = $this->getVerifiedDomains();
        $defaultCurrency = $this->getDefaultCurrency();

        return Inertia::render('affiliate/pages/CampaignSetup', compact('domains', 'defaultCurrency'));
    }

    /**
     * @param \App\AffiliateMemberCampaign $campaign
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroyCampaign(AffiliateMemberCampaign $campaign)
    {
        $campaign->delete();
        return response()->noContent();
    }

    /**
     * Affiliate member settings page
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showAffiliateSettings()
    {
        $accountId = $this->getCurrentAccountId();
        $settings = AffiliateMemberSetting::findOneOrCreateDefault($accountId);
        $currency = Currency
            ::where([
                'account_id' => $accountId,
                'isDefault' => 1
            ])
            ->first()
            ->currency ?? '$';
        return Inertia::render('setting/pages/AffiliateMemberSettings', compact('settings', 'currency'));
    }

    /**
     * Update affiliate member settings
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateAffiliateSettings(Request $request): Response
    {
        $request->validate([
            'id' => 'required|int',
            'auto_approve_affiliate' => 'required',
            'auto_approve_commission' => 'required',
            'auto_approval_period' => 'required|numeric|min:1',
            'auto_create_account_on_customer_sign_up' => 'required',
            'minimal_payout' => 'required|numeric|min:1',
            'enable_lifetime_commission' => 'required',
            'cookie_expiration_time' => 'required|numeric|min:1',
        ]);

        AffiliateMemberSetting
            ::findOrFail($request->id)
            ->update($request->except('account_id'));

        return response()->noContent();
    }

    /**
     * To override the problematic getCurrentAccountId in AuthAccountTrait
     *
     * @return mixed
     */
    private function getCurrentAccountId()
    {
        return Auth::user()->currentAccountId;
    }

    public function showAffiliateMemberProfile($ref)
    {
        $accountId = $this->getCurrentAccountId();
        $currency = $this->getDefaultCurrency();
        $allCustomers = ProcessedContact
            ::where('account_id', $accountId)
            ->get();
        $availablePromotions = Promotion
            ::where('account_id', $accountId)
            ->where('promotion_method', 'manual')
            ->where('promotion_status', 'active')
            ->whereDate('start_date', '<=', Carbon::now())
            ->orderBy('created_at', 'DESC')
            ->get();
        $member = AffiliateMemberAccount
            ::with('participant', 'referrals')
            ->where([
                'account_id' => $accountId,
                'referral_identifier' => $ref
            ])
            ->first();
        $participant = $member->participant;
        $commissions = $participant->commissions;
        $usedPromotions = DB::table('affiliate_member_participant_promotion')->where('affiliate_member_participant_id', '!=', optional($participant)->id)->whereIn('promotion_id',  $availablePromotions->map(function ($el) {
            return $el->id;
        }))->get()->map(function ($used) {
            return Promotion::find($used->promotion_id)->discount_code;
        });
        $availablePromotions = $availablePromotions->filter(function ($promo) use ($usedPromotions) {
            return !$usedPromotions->contains($promo->discount_code);
        });
        // data to return
        $memberData = [
            'account_id' => $member->account_id,
            'first_name' => $member->first_name,
            'last_name' => $member->last_name,
            'email' => $member->email,
            'affiliate_id' => $member->referral_identifier,
            'promo_code' => $participant
                ->promotions
                ->pluck('discount_code')
                ->join(','),
            'promo_id' => $participant
                ->promotions
                ->pluck('id')
                ->join(','),
            'groups' => $participant->groups->pluck('title'),
            'address' => $member->address,
            'city' => $member->city,
            'state' => $member->state,
            'zipcode' => $member->zipcode,
            'country' => $member->country,
            'paypal_email' => $member->paypal_email,
        ];

        $affiliateMemberController = new AffiliateMemberController;

        $campaigns = AffiliateMemberCampaign
            ::with('conditionGroups', 'domain')
            ->where('account_id', $accountId)
            ->get()
            ->map(static function ($c) use ($member, $currency, $commissions, $affiliateMemberController) {
                $c->joined_date = $member->created_at->toDateString();
                $c->currency = $currency;
                $c->commission = $commissions->reduce($affiliateMemberController->sumUpCommissions(), 0);
                return $c;
            });
        $campaigns =  $participant->status === 'approved' ? $campaigns : [];
        // $people = $member->referrals->count();
        $uniqueViews = AffiliateMemberReferralClickLog::where('referral_identifier', $ref)->get();

        // $orders = Order::whereIn('id', $commissions->pluck('order_id')->unique())->get();

        $orders = $commissions->unique(function ($item) {
            return $item['order_id'] . $item['order_detail_id'];
        })->map(function ($commission) use ($currency) {
            $order = Order::without(['orderDetails', 'orderDiscount'])->find($commission->order_id) ?? collect();
            // if($commission->order_detail_id){
            //     $order->sale = $this->convertCurrency(OrderDetail::find($commission->order_detail_id)?->total ?? 0, $order->currency,true, true);
            // }else{
            //     $order->sale = $this->convertCurrency($order?->total ?? 0, $order->currency, true, true);
            // }
            $order->sale = $this->convertCurrency($order?->total ?? 0, $order->currency, true, true);
            return $order;
        })->values()->all();

        $contacts = collect($orders)
            ?->pluck('processedContact')
            ?->unique()
            ?->map(function ($contact) use ($orders) {
                $contactOrders = $contact->orders->intersect($orders);
                $contact->affOrders = $contactOrders;
                $contact->totalSales = $this->getTotalPrice($contactOrders->toArray(), false);
                return $contact;
            });
        // $totalSales = $this->getTotalPrice($orders->toArray(), false);
        $totalCommissions = $commissions
            ->reduce($this->sumUpCommissions(), 0);
        $pendingCommissions = $commissions
            ->where('status', 'pending')
            ->reduce($this->sumUpCommissions(), 0);
        $approvedCommissions = $commissions
            ->where('status', 'approved')
            ->reduce($this->sumUpCommissions(), 0);
        // dd($paidCommissions = $participant->commissions
        // ->where('status', 'approved'));
        $paidCommissions = $participant->payouts
            ->where('status', 'paid')
            ->reduce($this->sumUpCommissions('amount'), 0,);
        $pendingPayouts = $participant->payouts
            ->where('status', 'pending')
            ->reduce($this->sumUpCommissions('amount'), 0);
        $disapprovedPayouts = $participant->payouts
            ->where('status', 'disapproved')
            ->reduce($this->sumUpCommissions('amount'), 0);
        $disapprovedCommissions = $commissions
            ->where('status', 'disapproved')
            ->reduce($this->sumUpCommissions(), 0);

        foreach ($participant->payouts as $payout) {
            $payoutAmount = $payout->amount;
            $payoutStatus = $payout->status;

            if ($payoutStatus === 'paid' || $payoutStatus === 'disapproved') {
                $approvedCommissions -= $payoutAmount;
            }
        }
        $levels = $participant->getSublines() ?? [];

        return Inertia::render(
            'affiliate/pages/Profile',
            compact(
                'availablePromotions',
                'allCustomers',
                'currency',
                'memberData',
                'campaigns',
                // 'people',
                'contacts',
                'uniqueViews',
                'orders',
                'totalCommissions',
                'pendingCommissions',
                'approvedCommissions',
                'paidCommissions',
                'pendingPayouts',
                'disapprovedPayouts',
                'disapprovedCommissions',
                'levels'
            )
        );
    }

    private function sumUpCommissions($type = 'commission')
    {
        return function ($carry, $c) use ($type) {
            return $carry + $this->convertCurrency(
                $c[$type],
                $c->currency ?? $this->defaultCurrency,
                true,
                true
            );
        };
    }

    public function updateMemberProfile(Request $request)
    {
        $accountId = $this->getCurrentAccountId();
        $member = AffiliateMemberAccount
            ::where([
                'account_id' => $accountId,
                'email' => $request->email
            ])
            ->first();

        if (empty($request->promo_code) || $request->promo_code === 'none') {
            $member->participant->promotions()->detach();
        }
            
        $promotion = Promotion
            ::where([
                'account_id' => $accountId,
                'discount_code' => $request->promo_code
            ])
            ->first();

        $member->update($request->except(['account_id', 'email', 'promo_code']));
        if ($promotion) $member->participant->promotions()->syncWithoutDetaching([$promotion->id]);

        return response()->noContent();
    }
}
