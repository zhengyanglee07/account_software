<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Traits\PublishedPageTrait;
use App\Traits\ReferralCampaignTrait;
use Inertia\Inertia;
use App\ReferralCampaign;
use App\SaleChannel;
use App\funnel;
use App\Account;
use App\ProcessedContact;
use App\ReferralSocialShareClickLog;
use App\Models\Promotion\Promotion;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EcommerceReferralController extends Controller
{
    use PublishedPageTrait, ReferralCampaignTrait;

    public function user()
    {
        return Auth::guard('ecommerceUsers')->user();
    }

    public function index()
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();
        $accountId = $publishPageBaseData['domain']->account_id;
        if($publishPageBaseData['domain']->type==='funnel') abort(404);
        $saleChannel = SaleChannel::where('type', $publishPageBaseData['domain']->type)->first()->id;
        $referralCampaign = ReferralCampaign::ignoreAccountIdScope()->where('account_id', $accountId)->where('sale_channel_id', $saleChannel)->first();
        if($referralCampaign){
            $referralCampaign->actions = $referralCampaign->actions();
            $referralCampaign->inviteeActions = $referralCampaign->inviteeActions();
            $referralCampaign->rewards = $referralCampaign->rewards()->map(function($reward){
                if ($reward->type==='promo-code') $reward->promoCode = Promotion::find($reward->value)->discount_code;
                return $reward;
            });
            $referralCampaign->email_message = json_decode($referralCampaign->email_message ?? '');
			$referralCampaign->social_message = json_decode($referralCampaign->social_message ?? '');
            $referralCampaign->referralSocialNetworks = $referralCampaign->referralSocialNetworks();
            $pid = Auth::guard('ecommerceUsers')->user()->processedContact->id;
            $referralCampaign->referralCode = $this->encodeReferralCode($pid, $referralCampaign->reference_key);
        }
        $processedContact =   $publishPageBaseData['customerInfo'] ?  $publishPageBaseData['customerInfo']->id : null;
        if($referralCampaign){
            if(Carbon::now()->lt($referralCampaign->active_date)){
                $referralCampaign = null;
            }else {
                if($referralCampaign->end_date){
                    if(Carbon::now()->gt($referralCampaign->end_date)){
                        $referralCampaign = null;
                    }
                }
            }
        }
        if(!optional($referralCampaign)->status) $referralCampaign = null;
        $pivotData = DB::table('processed_contact_referral_campaign')->where(['processed_contact_id'=> $processedContact, 'referral_campaign_id' => optional($referralCampaign)->id])->first();
        if($pivotData) $userReferralPoints = $pivotData->point;
        $logData = DB::table('processed_contact_referral_action_logs')->select('action_id')->where('contact_id', $processedContact)->get()->pluck('action_id');
        $referOrderCount = DB::table('referral_invitee_orders')->select('order_id')->where('refer_by', $processedContact)->get()->count();
        $referSignUpCount = DB::table('processed_contact_referral_campaign')->where(['refer_by'=> $processedContact, 'referral_campaign_id' => $referralCampaign?->id])->get()->count();
        $isPurchased = DB::table('referral_invitee_orders')->where('processed_contact_id', $processedContact)->where('referral_campaign_id', $referralCampaign?->id)->first() || false;
        $isRootUser = !$pivotData?->refer_by;
        return Inertia::render(
            'customer-account/pages/Referral',
            array_merge(
                $publishPageBaseData,
                [
                    'referralCampaign' => $referralCampaign,
                    'userReferralPoints' => $userReferralPoints ?? null,
                    'referralActionInfo' => [
                        'joined' =>  $pivotData ? (Boolean)$pivotData->is_joined : false,
                        'logs'=> $logData ? $logData : null,
                        'orderCount'=> $referOrderCount,
                        'signUpCount' => $referSignUpCount,
                        'isPurchased' => $isPurchased,
                        'isRootUser' => $isRootUser,
                    ]
                ]
            )
        );
    }

    /**
     * Get user referral points.
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $email
     * @param string $campaign id
     * @return int
     * @throws \Exception
     */
    public function points(Request $request)
    {
        if(!$request->campaignId) return;
        $campaign = ReferralCampaign::find($request->campaignId);
        $pid = $this->decodeReferralCode($request->code ?? $request->cookie('funnel#user#'.$campaign->reference_key), $campaign->reference_key);
        $processedContact = ProcessedContact::where('id', $pid)->where('account_id', $campaign?->account_id)->first();
        $pivotData = DB::table('processed_contact_referral_campaign')->where(['processed_contact_id'=> $processedContact?->id, 'referral_campaign_id' => $request->campaignId])->first();
        $logData = DB::table('processed_contact_referral_action_logs')->select('action_id')->where('contact_id', $processedContact?->id)->get()->pluck('action_id');
        $referOrderCount = DB::table('referral_invitee_orders')->select('order_id')->where('refer_by', $processedContact?->id)->get()->count();
        $referSignUpCount = DB::table('processed_contact_referral_campaign')->where(['refer_by'=> $processedContact?->id, 'referral_campaign_id' => $request->campaignId])->get()->count();
        $isPurchased = DB::table('referral_invitee_orders')->where('processed_contact_id', $processedContact?->id)->where('referral_campaign_id',$request->campaignId)->first()|| false;
        $isRootUser = !$pivotData?->refer_by;

        return response()->json([
            'points' =>  $pivotData ? $pivotData?->point : 0,
            'joined' =>  $pivotData ? $pivotData?->is_joined : false,
            'logData'=> $logData ? $logData : null,
            'referOrderCount'=> $referOrderCount ?? 0,
            'referSignUpCount' => $referSignUpCount ?? 0,
            'isPurchased'=> $isPurchased ?? false,
            'isRootUser' => $isRootUser ?? true,
            'isReferralUser' => !!$processedContact,
        ]);

    }

    /**
     * Get referral user.
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $email
     * @return Object referral user
     * @throws \Exception
     */
    public function isReferralUser(Request $request)
    {
        $funnel = funnel::ignoreAccountIdScope()->where('reference_key', $request->funnelId)->first();
        if(!$request->referralCode || !$funnel) return;
        $account = Account::find($funnel->account_id);
        $campaign = ReferralCampaign::where('account_id', $account->id)->where('funnel_id', $funnel->id)->first();
        if(!$campaign) return;
        $processedContactId = $this->decodeReferralCode($request->referralCode, $campaign->reference_key);
        $processedContact = ProcessedContact::find($processedContactId);
        $cookie = cookie('funnel#user#'.$campaign->reference_key, $request->referralCode, (3*30*24*60)); // 90 days
        return response()->json([
            'referralUser' =>  ['email'=> optional($processedContact)->email, 'referralCode'=>$request->referralCode],
        ])->cookie($cookie ??null);

    }

     /**
     * Track social share click
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return
     * @throws \Exception
     */
    public function clickSocialShare(Request $request)
    {
        $pid = $this->decodeReferralCode($request->referralCode, $request->refKey);
        $campaign = ReferralCampaign::where('reference_key', $request->refKey)->first();
        $processedContact = ProcessedContact::find($pid);
        if($campaign&&$processedContact){
            ReferralSocialShareClickLog::create([
                'processed_contact_id' => $processedContact->id,
                'referral_campaign_id' => $campaign->id,
                'type' => $request->type,
            ]);
        }
    }

}
