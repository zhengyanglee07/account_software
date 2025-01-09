<?php

namespace App;

use App\Models\Promotion\Promotion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Kalnoy\Nestedset\NodeTrait;
use App\Traits\ReferralCampaignTrait;
use Mail;

/**
 * Class AffiliateMemberParticipant
 * @package App
 *
 * @property int $id
 * @property int $account_id
 * @property int $affiliate_member_account_id
 * @property string $status
 * @property int $parent_id
 *
 * @mixin \Eloquent
 */
class AffiliateMemberParticipant extends Model
{
    use NodeTrait, ReferralCampaignTrait;

    protected $fillable = [
        'account_id',
        'affiliate_member_account_id',
        'status',
        '_lft',
        '_rgt',
        'parent_id'
    ];

    protected $with = ['member'];

    protected $appends = ['referralLinks', 'affiliateInvitationLink'];

    public function member(): BelongsTo
    {
        return $this->belongsTo(AffiliateMemberAccount::class, 'affiliate_member_account_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateMemberCommission::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(AffiliateMemberCommissionPayout::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(AffiliateMemberGroup::class);
    }

    /**
     * Only includes non-root participants of account with $accountId
     *
     * @param $query
     * @param $accountId
     * @return mixed
     */
    public function scopeNonRoot($query, $accountId)
    {
        return $query
            ->where('account_id', $accountId)
            ->whereNotNull('parent_id');
    }

    public function getReferralLinksAttribute(): Collection
    {
        $targetDomains = AffiliateMemberCampaign
            ::with('domain')
            ->where('account_id', $this->account_id)
            ->get()
            ->pluck('domain.domain')
            ->unique();

        return $targetDomains->map(function ($targetDomain) {
            $accountId = $this->account_id;
            $id = $this->member?->id;
            $identifierCode = $this->encodeReferralCode($id, $accountId);
            return "https://$targetDomain?aff=$identifierCode";
        });
    }

    public function getAffiliateInvitationLinkAttribute(): string
    {
        $affiliateMemberDomain = AccountDomain
            ::where([
                'account_id' => $this->account_id,
                'is_affiliate_member_dashboard_domain' => 1,
                'is_verified' => 1
            ])
            ->first();

        if (!$affiliateMemberDomain) {
            return '';
        }
        $accountId = $this->account_id;
        $id = $this->member?->id;
        $identifierCode = $this->encodeReferralCode($id, $accountId);
        return "https://{$affiliateMemberDomain->domain}/affiliates/signup?via={$identifierCode}";
    }

    public function pendingPayouts()
    {
        return $this->payouts->where('status', 'pending');
    }

    public function getSublines(){
        $depth = 1;
        AffiliateMemberCampaign::where('account_id', $this->account_id)->get()->map(function ($campaign)use(&$depth) {
            foreach ($campaign->conditionGroups as $condition) {
                if ($condition->groups->count() > 0) {
                    $specificGroups = $condition->groups->map(function ($group) {
                        return $group->id;
                    });
                    foreach ($this->groups->map(function ($group) {
                        return $group->id;
                    }) as $participantGroup) {
                        if ($specificGroups->contains($participantGroup)) {
                            $depth = $condition?->levels?->count() ?? 1;
                            return $campaign;
                        }
                    }
                } else return $campaign;
            }
        });
        $levels = [];
        $traverse = function ($nodes, $prefix = '-', $i = -1,) use (&$levels, &$traverse) {
            $i = $i + 1;
            if (!($levels[$i] ?? null)) {
                $levels[$i] = [];
            }
            foreach ($nodes as $node) {
                $content = $prefix . ' ' . $node->id;
                $traverse($node->children, $prefix . '-', $i,);
                array_push($levels[$i], $content);
            }
        };
        $nodes = $this->descendants->toTree();
        $traverse($nodes);
        $levels = array_filter(
            $levels,
            function ($key ) use(&$depth){
                return $key < $depth;
            },
            ARRAY_FILTER_USE_KEY
        );
        return $levels;
    }

    public function sendReferEmail(){
        $member = $this?->member ?? null;
        if($member){
            Mail::to($member?->email)->send(new \App\Mail\AffiliateMemberReferEmail($member));
        }
    }
}
