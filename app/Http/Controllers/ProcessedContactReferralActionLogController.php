<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ProcessedContactReferralActionLog;
use App\ReferralCampaign;
use App\ProcessedContact;
use App\Traits\ReferralCampaignTrait;
use Illuminate\Support\Facades\DB;

class ProcessedContactReferralActionLogController extends Controller
{
    use ReferralCampaignTrait;
    //TODO: Track all referral action for procesed contacts, now only tracks "inviter" custom actions.
    public function store(Request $request){
        $processedContactId = $this->decodeReferralCode($request->code ?? $request->cookie('funnel#user#'.$request->ref), $request->ref);
        if(!$processedContactId || !$request->action) return;
        $actionLog = ProcessedContactReferralActionLog::where(['contact_id'=>$processedContactId, 'action_id'=>$request->action])->first();
        if($actionLog) return;

        ProcessedContactReferralActionLog::create([
            'contact_id' => $processedContactId,
            'action_id' =>$request->action,
        ]);

        $campaign = ReferralCampaign::where('reference_key', $request->ref)->first();
        $contact = ProcessedContact::find($processedContactId);
        $points = $this->updateProcessContactPoint($contact, $campaign, 'custom', null, null, null, $request->action);
        $pivotData = DB::table('processed_contact_referral_campaign')->where(['processed_contact_id'=> optional($contact)->id, 'referral_campaign_id' => $campaign->id])->first();
        $logData = DB::table('processed_contact_referral_action_logs')->select('action_id')->where('contact_id', optional($contact)->id)->get()->pluck('action_id');
        $isPurchased = DB::table('referral_invitee_orders')->where('processed_contact_id', optional($contact)->id)->first() || false;

        return response()->json([
            'points'=>$points,
            'joined' =>  $pivotData ? $pivotData->is_joined : false,
            'logData'=> $logData ? $logData : null,
            'isPurchased' => $isPurchased
        ]);

    }
}
