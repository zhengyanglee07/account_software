<?php

namespace App\Traits;

use App\ReferalInviteeOrder;
use App\ProcessedContact;
use App\ReferralCampaign;
use App\AccountDomain;
use App\SaleChannel;
use App\ReferralEmail;
use App\funnel;
use App\ReferralSentEmail;
use App\ReferralCampaignLandingPage;
use App\Page;

use Cookie;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Promotion\Promotion;
use App\ReferralInviteeOrder;
use Hashids\Hashids;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReferralNotificationEmail;

trait ReferralCampaignTrait
{
    public function getReferralCampaigns($accountId)
    {
        return ReferralCampaign::ignoreAccountIdScope()->where('account_id', $accountId)->get()->map(function ($referralCampaign) {
            $referralCampaign->actions = $referralCampaign->actions()?->sortByDesc('points')->values();
            // $referralCampaign->inviteeActions = $referralCampaign->inviteeActions()?->sortByDesc('points')->values();
            $referralCampaign->actions = $referralCampaign->actions()?->merge($referralCampaign->inviteeActions())?->sortByDesc('points')->values();
            $referralCampaign->email_message = json_decode($referralCampaign->email_message ?? '');
            $referralCampaign->social_message = json_decode($referralCampaign->social_message ?? '');
            $referralCampaign->referralSocialNetworks = $referralCampaign->referralSocialNetworks();
            $referralCampaign->rewards = $referralCampaign->rewards()->map(function ($reward) {
                if ($reward->type === 'promo-code') $reward->promoCode = Promotion::find($reward->value)?->discount_code ?? null;
                return $reward;
            })->filter(function ($value,) {
                return (($value->type === 'promo-code' && $value->promoCode) || $value->type !== 'promo-code');
            })?->sortBy('point_to_unlock')->values();
            if (Carbon::now()->lt($referralCampaign->active_date)) {
                $referralCampaign->status = false;
            } else {
                if ($referralCampaign->end_date) {
                    if (Carbon::now()->gt($referralCampaign->end_date)) {
                        $referralCampaign->status = false;
                    }
                }
            }
            return $referralCampaign;
        });
    }

    public function checkReferralCampaignAction($domain, $event, $funnelId = null, $invitee = null, $order = null)
    {
        parse_str(Cookie::get('referral'), $referral);
        $accountDomain = AccountDomain::where('domain', $domain)->first();
        $saleChannelType = $accountDomain->type === 'store' ? 'online-store' : $accountDomain->type;
        $saleChannelId = SaleChannel::where('type', $saleChannelType)->first()->id;
        $accountId = $accountDomain->account_id;

        $campaign = ReferralCampaign::ignoreAccountIdScope()->where(['account_id' => $accountId])
            ->when(!$funnelId, function ($query) use ($saleChannelId) {
                return $query->where('sale_channel_id', $saleChannelId);
            })
            ->when($funnelId, function ($query, $funnelId) {
                return $query->where('funnel_id', $funnelId);
            })->first();
        if (!$campaign) return;
        if (!$campaign->status) return;
        $referralCode = $referral['invite'] ?? $referral['referral'];
        $processedContactId = $this->decodeReferralCode($referralCode, $campaign->reference_key);
        $processedContact = ProcessedContact::find($processedContactId);
        if (!$processedContact) return;

        $actionTypes = $campaign->actions()->map(function ($el) {
            return $el->type;
        });
        if (!$actionTypes->contains($event)) return;
        if (Carbon::now()->gt($campaign->active_date)) {
            if ($campaign->end_date) {
                if (Carbon::now()->lt($campaign->end_date)) {
                    $this->updateProcessContactPoint($processedContact, $campaign, $event, $invitee, $order, $domain);
                }
            } else $this->updateProcessContactPoint($processedContact, $campaign, $event, $invitee, $order, $domain);
        }
    }

    public function referralUser($processedContact, $funnelId)
    {
        if ($funnelId) {
            $campaign = ReferralCampaign::ignoreAccountIdScope()->where('account_id', $processedContact->account_id)->where('funnel_id', $funnelId)->first();
            if ($campaign) {
                if ($campaign->status && Carbon::now()->gt($campaign->active_date)) {
                    if ($campaign->end_date) {
                        if (Carbon::now()->lt($campaign->end_date)) {
                            $processedContact->assignReferralCampaign($campaign);
                        }
                    } else $processedContact->assignReferralCampaign($campaign);
                }
            } else return;
        } else return;
        $processedContact->referralCode = $processedContact->referralCode($funnelId);
        return ['email' => $processedContact->email, 'referralCode' => $processedContact->referralCode, 'campaign' => $campaign->reference_key];
    }

    public function newSignUp($processedContact, $funnelId)
    {
        if ($funnelId) {
            $campaign = ReferralCampaign::ignoreAccountIdScope()->where('account_id', $processedContact->account_id)->where('funnel_id', $funnelId)->first();
            if ($campaign) {
                return $processedContact->newParticipant($campaign);
            }
            return false;
        }
        return false;
    }


    public static function decodeReferralCode($referralCode, $ref)
    {
        $len = strlen($referralCode);
        $hashids = new Hashids($ref, $len, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
        return $hashids->decodeHex($referralCode);
    }

    public function encodeReferralCode($id, $ref)
    {
        $hashids = new Hashids($ref, 8, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
        return $hashids->encodeHex($id);
    }

    public function updateProcessContactPoint($processedContact, $campaign, $event, $invitee = null, $order = null, $domain = null, $id = null)
    {
        $eventPoint = !$id ? $campaign->actions()->where('type', $event)->first()->points : $campaign->actions()->find($id)->points;
        $pivotData = DB::table('processed_contact_referral_campaign')->where(['processed_contact_id' => $processedContact->id, 'referral_campaign_id' => $campaign->id])->first();
        if ($pivotData) {
            $processedContact->referralCampaigns()->updateExistingPivot($campaign->id, [
                'point' => $pivotData->point + $eventPoint,
            ]);
        } else {
            $processedContact->referralCampaigns()->syncWithoutDetaching([$campaign->id => ['point' => $eventPoint]]);
        }
        //email function
        if ($event !== 'join' && $event !== 'custom') {
            $campaign->domain = $domain;
            $this->sendNotificationEmail($processedContact, $campaign, 'referral-success');
        }

        $rewardReferralEmail = ReferralEmail::where([
            'referral_campaign_id' => $campaign->id,
            'type' => 'reward-unlocked'
        ])->first();

        foreach ($campaign->rewards() as $campaignReward) {
            if (
                ($pivotData->point >= $campaignReward->point_to_unlock) &&
                //check if ReferralSentEmail data is exist, if exist dont send again
                !(ReferralSentEmail::where([
                    'referral_email_id' => $rewardReferralEmail->id,
                    'processed_contact_id' => $processedContact->id,
                    'referral_campaign_reward_id' => $campaignReward->id
                ])->exists())
            ) {
                $campaign->rewardId = $campaignReward->id;
                $this->sendNotificationEmail($processedContact, $campaign, 'reward-unlocked');
            }
        }
        if ($invitee) $this->updateInviteePoint($campaign, $event, $invitee, $processedContact, $order);
        // Cookie::queue(Cookie::forget('referral'));
        return DB::table('processed_contact_referral_campaign')->where(['processed_contact_id' => $processedContact->id, 'referral_campaign_id' => $campaign->id])->first()->point;
    }

    public function updateInviteePoint($campaign, $event, $invitee, $inviter, $order)
    {
        $eventPoint = optional($campaign->inviteeActions()->where('type', $event)->first())->points ?? 0;
        $pivotData = DB::table('processed_contact_referral_campaign')->where(['processed_contact_id' => $invitee->id, 'referral_campaign_id' => $campaign->id])->first();
        if ($pivotData) {
            $invitee->referralCampaigns()->updateExistingPivot($campaign->id, [
                'point' => $pivotData->point + $eventPoint,
            ]);
        } else {
            $invitee->referralCampaigns()->syncWithoutDetaching([$campaign->id => ['point' => $eventPoint]]);
        }
        // //email function
        // if ($event !== 'join') {
        //     $campaign->domain = $domain;
        //     $this->sendNotificationEmail($invitee, $campaign, 'referral-success');
        // }
        if ($order) {
            ReferralInviteeOrder::create([
                'processed_contact_id' => $invitee->id,
                'order_id' => $order->id,
                'referral_campaign_id' => $campaign->id,
                'refer_by' => $inviter->id,
            ]);
        }
    }
    public function sendNotificationEmail($processedContact, $campaign, $toSendEmailType)
    {
        $referralEmail = ReferralEmail::where([
            'referral_campaign_id' => $campaign->id,
            'type' => $toSendEmailType
        ])->first();

        //return if email setting is disabled

        if (!optional($referralEmail)->is_enabled || !$campaign->status) return;

        $domain = isset($campaign->domain) && !($campaign->funnel_id)
            ? $campaign->domain
            : $this->getDomain($campaign);

        $referralCode = $this->encodeReferralCode($processedContact->id, $campaign->reference_key);

        $referralEmail->campaignDomain = $domain;
        $referralEmail->sharedPageDomain =  isset($campaign->domain) && !($campaign->funnel_id)
            ? $domain
            : $this->getDomain($campaign, 'funnel');
        $referralEmail->referralCode = $referralCode;

        $mailer = app()->environment('local') ? 'smtp' : 'ses-markt';


        Mail::mailer($mailer)->to($processedContact->email)->send(new ReferralNotificationEmail(
            $referralEmail,
            $processedContact,
            $campaign,
        ));
    }

    public function getDomain($campaign, $type = null)
    {
        $salesChannel = SaleChannel::find($campaign->sale_channel_id);
        $domain = AccountDomain::ignoreAccountIdScope()->where(['account_id' => $campaign->account_id,])
            ->when($campaign->funnel_id && $salesChannel->type === 'funnel', function ($query) use ($salesChannel, $campaign) {
                return $query->where(['type' => $salesChannel->type, 'type_id' => $campaign->funnel_id]);
            })
            ->when($salesChannel->type !== 'funnel', function ($query) use ($salesChannel) {
                return $query->where('type', $salesChannel->type,);
            })->first();

        if ($salesChannel->type === 'funnel' && isset($campaign->funnel_id)) {
            $funnel = funnel::ignoreAccountIdScope()->find($campaign->funnel_id);
            $funnelDomain = $domain ? $domain->domain : $funnel?->domain_name;
            $funnelLandingPage = $funnel->landingpages()->where('is_published', true)->orderBy('index')->first();
            $referralCampaignLandingPage = ReferralCampaignLandingPage::where('referral_campaign_id', $campaign->id)->first()?->landingPage()
                ?? $funnel->landingpages()->where('is_published', true)->where('index', '>', $funnelLandingPage->index)->first();
            $landingPage = $type === 'funnel'
                ? $funnelLandingPage
                : $referralCampaignLandingPage;
            if ($type === 'funnel')   $domain = $domain ? $domain->domain :  $funnelDomain . '/' . $landingPage->path;
            else $domain = $funnelDomain . '/' . $landingPage->path;
        } else if ($domain) {
            $domain = $domain->domain;
        }

        return $domain;
    }
}
