<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\ReferralCampaign;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\SaleChannel;
use App\funnel;
use App\Models\Promotion\Promotion;
use Illuminate\Support\Str;
use Auth;
use App\Account;
use App\Page;
use Illuminate\Support\Carbon;
use App\ReferralCampaignReward;
use App\ReferralCampaignRewardType;
use App\ReferralCampaignAction;
use App\ReferralCampaignActionType;
use App\ReferralEmail;
use App\ReferralSentEmail;
use App\ReferralCampaignPrize;
use App\ReferralInviteeAction;
use App\ReferralCampaignLandingPage;
use App\ProcessedContact;
use App\AccountDomain;
use App\SocialMediaProvider;
use Illuminate\Support\Facades\DB;
use App\Traits\ReferralCampaignTrait;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReferralTestEmail;
use App\Traits\PublishedPageTrait;
use App\Jobs\SendReferralRegisterSuccessJob;
use App\Services\EmailService;

class ReferralCampaignController extends Controller
{
    use ReferralCampaignTrait, PublishedPageTrait;
    /**
     * @return referral campaign dashboard page
     */
    public function index()
    {
        $user = Auth::user();
        $accountId = $user->currentAccountId;
        $referralCampaigns = ReferralCampaign::get()->map(function ($campaign) use ($accountId) {
            $campaign->salesChannel = Str::of(SaleChannel::find($campaign->sale_channel_id)->type)->replace('-', ' ')->title();
            $processedContacts = ProcessedContact::where('account_id', $accountId)->whereNotNull('email')->get();
            if ($campaign->salesChannel == 'Funnel') {
                $participantIds = DB::table('processed_contact_referral_campaign')->where(['referral_campaign_id' => $campaign->id])->get();
                $campaign->participantCount  = count($processedContacts->whereIn('id', $participantIds->pluck('processed_contact_id'))->values());
            } else
                $campaign->participantCount = $processedContacts->count();
            if (SaleChannel::find($campaign->sale_channel_id)->type === 'funnel') {
                $campaign->funnelName = optional(funnel::find($campaign->funnel_id))->funnel_name;
            }
            $campaign->actions = $campaign->actions()->map(function ($action) {
                $action->title = $action->type !== 'custom' ? $this->actionTitle($action->type) : $action->title;
                return $action;
            });
            $campaign->inviteeActions = $campaign->inviteeActions()->map(function ($action) {
                $action->title = $this->inviteeActionTitle($action->type);
                return $action;
            });
            $campaign->rewards = $campaign->rewards()->map(function ($reward) {
                $reward->typeTitle = $reward->type === 'promo-code' ? 'Promo Code' : 'Content';
                if ($reward->type === 'promo-code') {
                    $promotion = Promotion::find($reward->value);
                    if ($promotion) $reward->promoCode = $promotion->discount_code;
                }
                return $reward;
            });
            $saleChannels = Account::find($accountId)->saleChannels->map(function ($el) {
                return $el->id;
            });
            if (!$saleChannels->contains($campaign->sale_channel_id)) $campaign->status = 'inactive';
            if (Carbon::now()->gt($campaign->active_date)) {
                if ($campaign->end_date) {
                    if (Carbon::now()->gt($campaign->end_date)) {
                        $campaign->status = 'expired';
                    }
                }
            } else $campaign->status = 'inactive';
            if (optional(SaleChannel::find($campaign->sale_channel_id))->type === 'funnel' && !funnel::find($campaign->funnel_id))  $campaign->status = 'inactive';
            return $campaign;
        });
        return Inertia::render('referral/pages/Campaigns', compact('referralCampaigns'));
    }

    /**
     * @return referral show create/update campaign page
     */
    public function show($ref)
    {
        $user = Auth::user();
        $accountId = $user->currentAccountId;
        $account = Account::find($accountId);
        $accountTimeZone =  $account->timeZone;
        // variables used for filtering
        $saleChannelId = null;
        $funnelId = null;

        $emailTemplates = null;

        $campaign = ReferralCampaign::where('reference_key', $ref)->first();
        $funnelChannelId = SaleChannel::where('type', 'funnel')->first()->id;
        if ($campaign) {
            $saleChannelId = $campaign->sale_channel_id;
            $funnelId = $campaign->funnel_id;
            $campaign = $this->restructure($campaign);
            $processedContacts = ProcessedContact::where('account_id', $accountId)->whereNotNull('email')->get()->map(function ($contact) use ($campaign, $accountTimeZone) {
                $pivotData = DB::table('processed_contact_referral_campaign')->where(['processed_contact_id' => $contact->id, 'referral_campaign_id' => $campaign->id])->first();
                $contact->point = $pivotData ? $pivotData->point : 0;
                $createdTime =  new Carbon(optional($pivotData)->created_at ?? $campaign->created_at);
                $contact->joinDate = $createdTime->timezone($accountTimeZone)->isoFormat('DD/MM/YYYY');
                $contact->joinTime = $createdTime->timezone($accountTimeZone)->isoFormat('h:mm A');
                return $contact;
            });

            $emailTemplates =  ReferralEmail::where('referral_campaign_id', $campaign->id)->get();
            if ($funnelId || optional(SaleChannel::find($saleChannelId))->type === 'funnel') {
                $participantIds = DB::table('processed_contact_referral_campaign')->where(['referral_campaign_id' => $campaign->id])->get();
                $processedContacts = $processedContacts->whereIn('id', $participantIds->pluck('processed_contact_id'))->values();
            }
        }

        $referralSalesChannels = ReferralCampaign::select('sale_channel_id')
            ->where('sale_channel_id', '!=',   $funnelChannelId)
            ->where('sale_channel_id', '!=',   $saleChannelId)
            ->get()->map(function ($el) {
                return $el->sale_channel_id;
            });
        $referralFunnels = ReferralCampaign::select('funnel_id')->where('funnel_id', '!=', $funnelId)->get()->map(function ($el) {
            return $el->funnel_id;
        });
        $funnels = funnel::get()->filter(function ($value) use ($referralFunnels) {
            return !$referralFunnels->contains($value->id);
        })->values();
        $salesChannels = Account::find($accountId)->saleChannels->filter(function ($value) use ($referralSalesChannels, $funnels, $saleChannelId) {
            return
                !$referralSalesChannels->contains($value->id) && (($funnels->count() > 0 || optional(SaleChannel::find($saleChannelId))->type === 'funnel') ? $value->type !== 'shopee' :   $value->type !== 'shopee' && $value->type !== 'funnel');
        })->map(function ($e) {
            $e->name = Str::of($e->type)->replace('-', ' ')->title();
            return $e;
        });
        $promotions = Promotion::where(['account_id' => $accountId,])->whereNotNull('discount_code')->get();
        $metaInfo = ReferralCampaign::metaInfo($account);
        return Inertia::render('referral/pages/CampaignSetup', [
            'campaign' => $campaign ?? null,
            'salesChannels' => $salesChannels ?? null,
            'funnels' => $funnels ?? null,
            'promotions' => $promotions ?? null,
            'processedContacts' => $processedContacts ?? null,
            'emailTemplates' => $emailTemplates ?? null,
            'metaInfo' => $metaInfo ?? null,
        ]);
    }

    /**
     * @return status
     * to create the new referral campaign
     */
    public function store(Request $request, EmailService $emailService)
    {
        $request->validate([
            'title' => 'required|string',
            'salesChannel' => 'required|in:mini-store,online-store,funnel',
            'funnel' => 'required_if:salesChannel,funnel',
            'actions' => 'required|array',
            // 'rewards' => 'required|array',
            'activeDate' => 'required|date',
            'isExpiry' => 'boolean',
            'endDate' => 'required_if:isExpiry,true',
        ]);

        $saleChannelId = SaleChannel::where('type', $request->salesChannel)->first()->id;
        $accountId = Auth::user()->currentAccountId;
        $account = Account::findOrFail($accountId);
        $campaign = ReferralCampaign::create([
            'reference_key' => $this->getRefKey(),
            'title' => $request->title,
            'sale_channel_id' => $saleChannelId,
            'funnel_id' => $request->funnel,
            'status' => true,
            'is_expiry' => $request->isExpiry,
            'active_date' => Carbon::createFromFormat('Y-m-d H:i a', $request->activeDate, $account->timezone)
                ->setTimeZone('Asia/Kuala_Lumpur')
                ->format('Y-m-d H:i:s'),
            'end_date' => $request->endDate ? Carbon::createFromFormat('Y-m-d H:i a', $request->endDate, $account->timezone)
                ->setTimeZone('Asia/Kuala_Lumpur')
                ->format('Y-m-d H:i:s') : null,
            'social_message' => empty($request->socialMessage) ? null : json_encode($request->socialMessage),
            'email_subject' => empty($request->emailSubject) ? null : $request->emailSubject,
            'email_message' => empty($request->emailMessage) ? null : json_encode($request->emailMessage),
            'social_network_enabled' => $request->socialNetworkEnabled,
            'share_email_enabled' => $request->shareEmailEnabled,
        ]);
        $socialNetworks = SocialMediaProvider::get()->filter(function ($el) use ($request) {
            return in_array($el->name, $request->referralSocialNetworks);
        })->pluck('id');
        $campaign->socialNetworks()->sync($socialNetworks);
        foreach ($request->prizes as $prize) {
            ReferralCampaignPrize::create([
                'referral_campaign_id' => $campaign->id,
                'title' => $prize['prizeTitle'],
                'number_of_winner' => $prize['noOfWinner'],
            ]);
        }
        foreach ($request->actions as $action) {
            ReferralCampaignAction::create([
                'referral_campaign_id' => $campaign->id,
                'action_type_id' => ReferralCampaignActionType::where('type', $action['actionType'])->first()->id,
                'points' => $action['point'],
                'title' => empty($action['title']) ? null : $action['title'],
                'url' => empty($action['url']) ? null :  json_encode($action['url']),
            ]);
        }


        foreach ($request->inviteeActions as $action) {
            ReferralInviteeAction::create([
                'referral_campaign_id' => $campaign->id,
                'action_type_id' => ReferralCampaignActionType::where('type', $action['actionType'])->first()->id,
                'points' => $action['point'],
            ]);
        }

        foreach ($request->rewards as $reward) {
            ReferralCampaignReward::create([
                'referral_campaign_id' => $campaign->id,
                'reward_type_id' => ReferralCampaignRewardType::where('type', $reward['rewardType'])->first()->id,
                'title' => $reward['rewardTitle'],
                'value' => $reward['rewardValue'],
                'point_to_unlock' => $reward['pointToUnlock'],
                'text' => $reward['rewardValueText'],
                'instruction' => empty($reward['redemptionInstruction']) ? null : json_encode($reward['redemptionInstruction']),
            ]);
        }

        $this->saveWinner($campaign, $request);
        foreach ($request->emailTemplates as $emailTemplate) {
            ReferralEmail::create([
                'referral_campaign_id' => $campaign->id,
                'type' => $emailTemplate['type'],
                'subject' => $emailTemplate['subject'],
                'template' => $emailTemplate['template'],
                'is_enabled' => $request->emailStatus[$emailTemplate['type']],
            ]);
        }

        $campaign->updateReferralCampaignParticipant($campaign, 'created');

        // SendReferralRegisterSuccessJob::dispatch($campaign->processedContact()->get(), $campaign, 'register-success');
        $emailService->sendReferralNotificationEmail($campaign->processedContact()->get(), $campaign, 'register-success');
    }

    /**
     * @return status
     * to update or edit the referral campaign
     */
    public function update(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'salesChannel' => 'required|in:mini-store,online-store,funnel',
            'funnel' => 'required_if:salesChannel,funnel',
            'actions' => 'required|array',
            // 'rewards' => 'required|array',
            'activeDate' => 'required|date',
            'isExpiry' => 'boolean',
            'endDate' => 'required_if:isExpiry,true',
        ]);
        $saleChannelId = SaleChannel::where('type', $request->salesChannel)->first()->id;
        $accountId = Auth::user()->currentAccountId;
        $account = Account::findOrFail($accountId);
        $campaign = ReferralCampaign::find($request->id);
        $campaign->update([
            'title' => $request->title,
            'sale_channel_id' => $saleChannelId,
            'funnel_id' => $request->salesChannel == 'funnel' ? $request->funnel : null,
            'is_expiry' => $request->isExpiry,
            'active_date' => Carbon::createFromFormat('Y-m-d H:i a', $request->activeDate, $account->timezone)
                ->setTimeZone('Asia/Kuala_Lumpur')
                ->format('Y-m-d H:i:s'),
            'end_date' => $request->endDate ? Carbon::createFromFormat('Y-m-d H:i a', $request->endDate, $account->timezone)
                ->setTimeZone('Asia/Kuala_Lumpur')
                ->format('Y-m-d H:i:s') : null,
            'social_message' => empty($request->socialMessage) ? null : json_encode($request->socialMessage),
            'email_subject' => empty($request->emailSubject) ? null : $request->emailSubject,
            'email_message' => empty($request->emailMessage) ? null : json_encode($request->emailMessage),
            'social_network_enabled' => $request->socialNetworkEnabled,
            'share_email_enabled' => $request->shareEmailEnabled,
        ]);
        $socialNetworks = SocialMediaProvider::get()->filter(function ($el) use ($request) {
            return in_array($el->name, $request->referralSocialNetworks);
        })->pluck('id');
        $campaign->socialNetworks()->sync($socialNetworks);
        if (empty($request->prizes)) {
            $prevPrizes = ReferralCampaignPrize::where('referral_campaign_id', $campaign->id)->get()->map(function ($el) {
                return $el->id;
            });
            ReferralCampaignPrize::destroy($prevPrizes);
        }
        foreach ($request->prizes as $prize) {
            ReferralCampaignPrize::updateOrCreate(
                ['id' => $prize['id']],
                [
                    'referral_campaign_id' => $campaign->id,
                    'title' => $prize['prizeTitle'],
                    'number_of_winner' => $prize['noOfWinner'],
                ]
            );
        }
        $prevActions = ReferralCampaignAction::where('referral_campaign_id', $campaign->id)->get()->map(function ($el) {
            return $el->id;
        });
        $toRemoveActions = $prevActions->diff(collect($request->actions)->map(function ($action) {
            return $action['id'];
        }));
        ReferralCampaignAction::destroy($toRemoveActions);
        foreach ($request->actions as $action) {
            ReferralCampaignAction::updateOrCreate(
                ['id' => $action['id']],
                [
                    'referral_campaign_id' => $campaign->id,
                    'action_type_id' => ReferralCampaignActionType::where('type', $action['actionType'])->first()->id,
                    'points' => $action['point'],
                    'title' => empty($action['title']) ? null : $action['title'],
                    'url' => empty($action['url']) ? null :  json_encode($action['url']),
                ]
            );
        }
        $prevInviteeActions = ReferralInviteeAction::where('referral_campaign_id', $campaign->id)->get()->map(function ($el) {
            return $el->id;
        });
        ReferralInviteeAction::destroy($prevInviteeActions);
        foreach ($request->inviteeActions as $action) {
            ReferralInviteeAction::create([
                'referral_campaign_id' => $campaign->id,
                'action_type_id' => ReferralCampaignActionType::where('type', $action['actionType'])->first()->id,
                'points' => $action['point'],
            ]);
        }
        $prevRewards = ReferralCampaignReward::where('referral_campaign_id', $campaign->id)->get()->map(function ($el) {
            return $el->id;
        });
        ReferralCampaignReward::destroy($prevRewards);

        $sentRewardEmails = ReferralSentEmail::whereIn('referral_campaign_reward_id', $prevRewards->toArray())->get()->map(function ($el) {
            return $el->id;
        });
        ReferralSentEmail::destroy($sentRewardEmails);

        foreach ($request->rewards as $reward) {
            ReferralCampaignReward::create([
                'referral_campaign_id' => $campaign->id,
                'reward_type_id' => ReferralCampaignRewardType::where('type', $reward['rewardType'])->first()->id,
                'title' => $reward['rewardTitle'],
                'value' => $reward['rewardValue'],
                'point_to_unlock' => $reward['pointToUnlock'],
                'text' => $reward['rewardValueText'],
                'instruction' => empty($reward['redemptionInstruction']) ? null : json_encode($reward['redemptionInstruction']),
            ]);
        }

        $this->saveWinner($campaign, $request);
        foreach ($request->emailTemplates as $emailTemplate) {
            ReferralEmail::updateOrCreate(
                [
                    'id' => $emailTemplate['id'],
                    'type' => $emailTemplate['type']
                ],
                [
                    'referral_campaign_id' => $campaign->id,
                    'subject' => $emailTemplate['subject'],
                    'template' => $emailTemplate['template'],
                    'is_enabled' => $request->emailStatus[$emailTemplate['type']],
                ]
            );
        }

        $campaign->updateReferralCampaignParticipant($campaign, 'updated');
    }

    /**
     * @return
     * to delete the referral campaign
     */
    public function destroy($id)
    {
        $campaign = ReferralCampaign::find($id);
        $campaign->delete();
    }

    /**
     * @return
     * to update the referral campaign status
     */
    public function status($id)
    {
        $campaign = ReferralCampaign::find($id);
        $campaign->status = !$campaign->status;
        $campaign->save();
    }

    /**
     * @return
     * to fetch all the referral campaigns
     */
    public function fetch()
    {
        $accountId = Auth::user()->currentAccountId;
        return $this->getReferralCampaigns($accountId);
    }

    /**
     * @return
     * to select the prize winner
     */
    public function sweepStake($uuid, Request $request)
    {
        $campaign = ReferralCampaign::where('reference_key', $uuid)->first();
        if (!$campaign) return;
        $pool = [];
        $campaign->processedContact = $campaign->processedContact->filter(function ($el) use ($request) {
            return !in_array($el->contactRandomId, $request->acceptedWinners) && $el->email;
        });
        foreach ($campaign->processedContact as $processedContact) {
            $arr = array_fill(0, ($processedContact->pivot->point), $processedContact->id);
            $pool = array_merge($pool, $arr);
        }
        $x = 1;
        $winnerId = null;
        $winnerList = [];
        do {
            $pool = array_filter($pool, function ($el) use ($winnerId) {
                return $el != $winnerId;
            });
            if (empty($pool)) break;
            shuffle($pool);
            $winnerId = $pool[0];
            $processedContact = ProcessedContact::find($winnerId);
            $winnerList[] = $processedContact->contactRandomId;
            $x++;
        } while ($x <= ($request->noOfWinner - count($request->acceptedWinners)));

        return response()->json($winnerList);
    }

    /**
     * @return
     * to save the prize winner
     */
    public function saveWinner($campaign, Request $request)
    {
        $prize = $campaign->prizes();
        if (!$campaign || !$prize) return;
        foreach ($request->acceptedWinners as $winner) {
            $processedContact  = ProcessedContact::find($winner);
            if (!$prize->processedContacts->contains($processedContact->id)) {
                $prize->processedContacts()->attach($processedContact->id);
            }
        }
        foreach ($request->suggestedWinners as $suggestedWinner) {
            $processedContact  = ProcessedContact::find($suggestedWinner);
            if ($prize->processedContacts->contains($processedContact->id)) {
                $prize->processedContacts()->detach($processedContact->id);
            }
        }
    }

    public function getFunnelDomain($funnel, $accountId)
    {
        $funnelDomain =  AccountDomain::ignoreAccountIdScope()->where([
            'type' => 'funnel',
            'account_id' => $accountId,
            'type_id' => $funnel->id,
        ])->first()?->domain;
        if (!$funnelDomain) {
            $path = $funnel->landingpages()->where('is_published', true)->orderBy('index')->first()->path;
            $funnelDomain = $path ? $funnel->domain_name . '/' . $path : $funnel->domain_name;
        }
        $protocol = app()->environment() === 'local' ? 'http://' : 'https://';
        $domain = $protocol . $funnelDomain;
        return $domain;
    }


    /**
     * @return
     * to save the funnel referral landing page
     */
    public function saveReferralLandingPage(Request $request)
    {
        $campaign = ReferralCampaign::ignoreAccountIdScope()->where('reference_key', $request->campaign)->first();
        $landingPage = Page::where('reference_key', $request->landingPage)->first();
        if (!$campaign || !$landingPage) return;
        $referralLanding = ReferralCampaignLandingPage::where('referral_campaign_id', $campaign->id)->first();
        $funnelUser  = $request->cookie('funnel#user#' . $campaign->reference_key);
        $contact = null;
        if ($funnelUser) {
            $pid = $this->decodeReferralCode($funnelUser, $campaign->reference_key);
            $contact = ProcessedContact::find($pid);
        }
        ReferralCampaignLandingPage::updateOrCreate(
            ['id' => optional($referralLanding)->id],
            [
                'referral_campaign_id' => $campaign->id,
                'landing_page_id' => $landingPage->id,
            ]
        );
        $funnel = funnel::ignoreAccountIdScope()->find($campaign->funnel_id);

        return response()->json([
            'domain' => $this->getFunnelDomain($funnel, $campaign->account_id),
            'referralCode' => $contact ? $funnelUser : null,
        ]);
    }

    private function generateUUID(): string
    {
        try {
            $uuid = Str::substr(Str::uuid(), 0, 13);
        } catch (\Exception $e) {
            \Log::error('Random refkey failed to generate: ' . $e);
            throw $e;
        }

        return $uuid;
    }

    /**
     * Get value to be stored in model's reference_key.
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $refKeyColumn
     * @return int
     * @throws \Exception
     */
    private function getRefKey(): string
    {
        do {
            $key = $this->generateUUID();
        } while (ReferralCampaign::ignoreAccountIdScope()->where('reference_key', $key)->exists());
        return $key;
    }

    private function actionTitle($value)
    {
        switch ($value) {
            case ('purchase'):
                return 'Refer a new customer';
            case ('sign-up'):
                return 'Refer a new sign up';
            default:
                return 'Join this referral campaign';
        }
    }
    private function inviteeActionTitle($value)
    {
        switch ($value) {
            case ('purchase'):
                return 'Become a new customer';
            default:
                return 'Become a new sign up';
        }
    }

    private function rewardTypeTitle($type)
    {
        switch ($type) {
            case 'downloadable-content':
                return "Custom Link";
            case 'custom-message':
                return "Custom Message";
            default:
                return "Promotion Code";
        }
    }

    private function restructure($campaign)
    {
        $user = Auth::user();
        $accountId = $user->currentAccountId;
        $account = Account::findOrFail($accountId);
        $campaign->actions = $campaign->actions()->map(function ($action) {
            $action->actionType = $action->type;
            $action->title = $action->type !== 'custom' ? $this->actionTitle($action->type) : $action->title;
            $action->point = $action->points;
            return $action;
        });
        $campaign->inviteeActions = $campaign->inviteeActions()->map(function ($action) {
            $action->actionType = $action->type;
            $action->title = $action->type === 'purchase' ? 'Become a new customer' : 'Become a new sign up';
            $action->point = $action->points;
            return $action;
        });
        $campaign->prizes = $campaign->prizes();
        $campaign->rewards = $campaign->rewards()->map(function ($reward) {
            $reward->rewardType = $reward->type;
            $reward->rewardTitle = $reward->title;
            $reward->typeTitle = $this->rewardTypeTitle($reward->type);
            $reward->pointToUnlock = $reward->point_to_unlock;
            $reward->rewardValue  = $reward->value;
            $reward->rewardValueText = $reward->text;
            $reward->redemptionInstruction = $reward->instruction;
            if ($reward->type === 'promo-code') {
                $promotion = Promotion::find($reward->value);
                if ($promotion) $reward->promoCode = $promotion->discount_code;
            }
            return $reward;
        });
        $campaign->sale_channel = ['id' => $campaign->sale_channel_id, 'type' => SaleChannel::find($campaign->sale_channel_id)->type];
        $campaign->emailedWinners = $campaign->grandPrizeEmails();
        $campaign->active_date = Carbon::createFromFormat('Y-m-d H:i:s', $campaign->active_date, 'Asia/Kuala_Lumpur')
            ->setTimeZone($account->timeZone)
            ->format('Y-m-d h:i a');
        $campaign->end_date = $campaign->end_date ? Carbon::createFromFormat('Y-m-d H:i:s', $campaign->end_date, 'Asia/Kuala_Lumpur')
            ->setTimeZone($account->timeZone)
            ->format('Y-m-d h:i a') : $campaign->end_date;
        $campaign->referralSocialNetworks = $campaign->referralSocialNetworks();
        return $campaign;
    }

    public function sendTestEmail(Request $request)
    {
        $user = Auth::user();
        $accountId = $user->currentAccountId;
        $mailer = app()->environment('local') ? 'smtp' : 'ses-markt';
        foreach ($request->emailAddresses as $emailAddress) {
            Mail::mailer($mailer)->to($emailAddress)->send(new ReferralTestEmail($request->emailData, $accountId));
        }
    }

    public function export(Request $request)
    {
        $fileName = 'participant.csv';
        $participants = $request->participants;

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('DATE', 'TIME', 'EMAIL', 'POINTS', 'FIRST NAME', 'LAST NAME', 'MOBILE NUMBER');

        $callback = function () use ($participants, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($participants as $participant) {
                $row['DATE']  = $participant['joinDate'];
                $row['TIME']  = $participant['joinTime'];
                $row['EMAIL']  = $participant['email'];
                $row['POINTS']    = $participant['point'];
                $row['FIRST NAME']    = $participant['fname'];
                $row['LAST NAME']    = $participant['lname'];
                $row['MOBILE NUMBER']    = $participant['phone'];

                fputcsv($file, array($row['DATE'], $row['TIME'], $row['EMAIL'], $row['POINTS'], $row['FIRST NAME'], $row['LAST NAME'], $row['MOBILE NUMBER']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
