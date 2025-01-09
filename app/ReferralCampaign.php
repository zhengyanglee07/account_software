<?php

namespace App;

use Auth;
use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;
use App\ReferralCampaignAction;
use App\ReferralCampaignActionType;
use App\ReferralCampaignReward;
use App\ReferralCampaignRewardType;
use App\ReferralInviteeAction;
use App\ReferralCampaignPrize;
use App\ReferralEmail;
use App\ReferralSentEmail;
use App\ProcessedContact;
use App\Page;
use App\MiniStore;
use App\SaleChannel;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferralCampaign extends Model
{
    use BelongsToAccount, SoftDeletes;
    protected $guarded = ['id'];

    public function saleChannelType()
    {
        return SaleChannel::find($this->sale_channel_id)->type;
    }
    public function actions()
    {
        return ReferralCampaignAction::with('actionType')->where('referral_campaign_id', $this->id)->get()->map(function ($action) {
            $action->type = optional($action->actionType)->type;
            $action->url = $action->url ? json_decode($action->url) : null;
            $action->referralType = 'inviter';
            return $action;
        });
    }
    public function rewards()
    {
        return ReferralCampaignReward::where('referral_campaign_id', $this->id)->get()->map(function ($reward) {
            $reward->type = ReferralCampaignRewardType::find($reward->reward_type_id)->type;
            $reward->instruction = json_decode($reward->instruction ?? '');
            return $reward;
        });
    }
    public function inviteeActions()
    {
        return ReferralInviteeAction::where('referral_campaign_id', $this->id)->get()->map(function ($action) {
            $action->type = ReferralCampaignActionType::find($action->action_type_id)->type;
            $action->referralType = 'invitee';
            return $action;
        });
    }
    public function prizes()
    {
        $prize =  ReferralCampaignPrize::where('referral_campaign_id', $this->id)->first();
        if ($prize) {
            $prize->prizeTitle = $prize->title ?? '';
            $prize->noOfWinner = $prize->number_of_winner ?? 1;
            $prize->winners = $prize->processedContacts->map(function ($el) {
                return $el->id;
            });
        }
        return $prize;
    }
    public function grandPrizeEmails()
    {
        $email =  ReferralEmail::where(['referral_campaign_id' => $this->id, 'type' => 'grand-prize'])->first();
        $grandPrizeEmails = [];
        if ($email) {
            $grandPrizeEmails =  ReferralSentEmail::where('referral_email_id', $email->id)->get()->map(function ($el) use ($grandPrizeEmails) {
                return optional(ProcessedContact::find($el->processed_contact_id))->contactRandomId;
            });
        }
        return $grandPrizeEmails;
    }
    public function processedContact()
    {
        return $this->belongsToMany('App\ProcessedContact')->withPivot('point', 'is_joined')->withTimestamps();
    }
    public function notJoinedProcessedContact()
    {
        return $this->belongsToMany('App\ProcessedContact')->withPivot('point', 'is_joined')->wherePivot('is_joined', false);
    }
    public function socialNetworks()
    {
        return $this->belongsToMany(SocialMediaProvider::class, 'referral_campaign_social_networks', 'referral_campaign_id', 'social_network_id');
    }
    public function referralSocialNetworks()
    {
        return $this->socialNetworks->map(function ($el) {
            return $el->name;
        });
    }
    public static function metaInfo($account)
    {
        return $account->activeSaleChannels->map(function ($channel) use ($account) {
            if ($channel->type === 'online-store') {
                $domain = AccountDomain::whereAccountId($account->id)->whereType('online-store')->first();
                $channel->metaInfo = Page::find(optional($domain)->type_id);
            }
            if ($channel->type === 'mini-store') {
                $domain = AccountDomain::whereAccountId($account->id)->whereType('mini-store')->first();
                $miniStore = MiniStore::where('account_id', $account->id)->first();
                $miniStore->seo_title = $miniStore->name;
                $miniStore->seo_meta_description = $miniStore->description;
                $channel->metaInfo = $miniStore;
            }
            if ($channel->metaInfo) {
                $channel->metaInfo->seo_title =  $channel->metaInfo->seo_title ?? $account->store_name;
                $channel->metaInfo->seo_meta_description =  $channel->metaInfo->seo_meta_description ?? 'Shop things you love at ' .   $account->store_name . '!';
                $channel->metaInfo->fb_image =  $channel->metaInfo->fb_image  ?? $account->company_logo;
            }
            if ($channel->type === 'funnel') {
                $domain = AccountDomain::whereAccountId($account->id)->whereType('funnel')->first() ??  AccountDomain::whereAccountId($account->id)->first();
                $channel->metaInfo = Page::whereAccountId($account->id)->where('funnel_id', '!=', null)->where('index', 0)->get()->map(function ($landing) use ($account) {
                    $landing->seo_title = $landing->seo_title ?? $account->store_name;
                    $landing->seo_meta_description = $landing->seo_meta_description ?? 'Shop things you love at ' .   $account->store_name . '!';
                    $landing->fb_image =  $landing->fb_image  ?? $account->company_logo;
                    return $landing;
                });
            }
            $channel->domain = optional($domain ?? null)->domain;

            return $channel;
        });
    }
    public function updateReferralCampaignParticipant($campaign, $type)
    {
        $account = Auth::user()->currentAccount();
        $proccessContact = $account->processedContacts->map(function ($people) {
            return $people->id;
        });
        $campaign->actions = $campaign->actions()->map(function ($el) {
            return $el->type;
        });
        if ($campaign->actions->contains('join') && !$campaign->funnel_id) {
            $eventPoint = $campaign->actions()->where('type', 'join')->first()->points;
            if ($type === 'created') {
                $campaign->processedContact()->syncWithPivotValues($proccessContact, ['point' => $eventPoint, 'is_joined' => true]);
            } else if ($type === 'updated') {
                $campaign->notJoinedProcessedContact->map(function ($people) use ($eventPoint) {
                    $people->pivot->point = $people->pivot->point + $eventPoint;
                    $people->pivot->is_joined = true;
                    $people->pivot->save();
                });
            }
        }
    }
    public static function boot()
    {
        parent::boot();

        function updatePlanTotal()
        {
            $account = Auth::user()->currentAccount();
            $accountPlan = $account->accountPlanTotal;
            $accountPlan->total_referral_campaign = $account->referralCampaign->count();
            $accountPlan->save();
        };

        static::created(function () {
            updatePlanTotal();
        });

        static::deleted(function () {
            updatePlanTotal();
        });
    }
}
