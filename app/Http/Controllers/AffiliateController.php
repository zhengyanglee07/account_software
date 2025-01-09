<?php

namespace App\Http\Controllers;

use App\AffiliateCommissionLog;
use App\AffiliateClickLog;
use Auth;
use App\AffiliateReferralLog;
use App\Account;
use App\AffiliateDetail;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Mail\AffiliateSignupEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PDO;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdateAffiliatePassword;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Hashids\Hashids;

class AffiliateController extends Controller
{
    // public function emailConfirmation()
    // {
    //     $user = Auth::user();
    //     $email = $user->email;

    //     // return view('confirmEmail', compact('email'));
    // }
    private function affiliateUser()
    {
        $affiliate  = Auth::guard('affiliateusers')->user();
        $affiliate->tier = $affiliate->tier();
        $affiliate->successReferralCount = $affiliate->successReferralCount();
        return $affiliate;
    }

    public function viewDashboard()
    {
        if (Auth::guard('affiliateusers')->check() == 'true') {
            $affiliate_user = $this->affiliateUser();
            $hashids = new Hashids('affiliate.hypershapes.com', 8, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
            $affiliate_user->affLinkCode = $hashids->encodeHex($affiliate_user->id);
            $affiliate_detail = AffiliateDetail::where('affiliate_userid', $affiliate_user->id)->first();
            $affiliate_detail->current_free_user = count(Account::where(['status' => 'active', 'refer_by' => $affiliate_user->affiliate_unique_id, 'subscription_plan_id' => 1])->get());
            $affiliate_detail->total_referrals = AffiliateReferralLog::where('affiliate_id', $affiliate_user->id)->where('user_id', '!=', NULL)->where('parent_id', '!=', NULL)->count();
            $affiliate_log = DB::table('affiliate_referral_logs')->where('affiliate_id', $affiliate_user->id)
                ->join('subscriptions', 'affiliate_referral_logs.subscription_id', '=', 'subscriptions.id')
                ->join('subscription_plans', 'subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
                ->select('affiliate_referral_logs.*', 'subscriptions.status', 'subscription_plans.plan')
                ->get()->map(function ($affiliateLog) {
                    $affiliateLog->referral_name = $this->latestName($affiliateLog->user_id);
                    return $affiliateLog;
                });
            $referralArr = $affiliate_user->affiliateReferrals->where('user_id', '!=', NULL)->where('parent_id', '!=', NULL)->map(function ($affiliateReferral) {
                $affiliateReferral->referral_name = $affiliateReferral->user->firstName . ' ' . $affiliateReferral->user->lastName;
                return $affiliateReferral;
            });
            $affiliateReferrals = [];
            $affiliateSubscription = [];
            foreach ($referralArr as $referral) {
                array_push($affiliateReferrals, $referral);
                if ($referral->subscription) {
                    array_push($affiliateSubscription, $referral->subscription);
                }
            }
            if (count($affiliateSubscription) > 0) {
                $filteredFreePlan = array_filter($affiliateSubscription, function ($obj) {
                    return $obj->subscription_plan_id == 1;
                });
                $filteredPaidPlan = array_filter($affiliateSubscription, function ($obj) {
                    return $obj->subscription_plan_id > 1 && ($obj->status == 'active' || $obj->status == 'trialing');
                });
                $affiliate_detail->current_free_user = count($filteredFreePlan);
                $affiliate_detail->current_paid_account = count($filteredPaidPlan);
            }
            $affiliateCommissionLogs = $affiliate_user->affiliateCommissionLogs->map(function ($commissionLog) use ($referralArr) {
                $commissionLog->affiliate_referral = $referralArr->where('id', $commissionLog->referral_id)->first();
                if(!$commissionLog->affiliate_referral){
                    $commissionLog->affiliate_referral = AffiliateReferralLog::find($commissionLog->referral_id);
                    $commissionLog->affiliate_referral->referral_name = $commissionLog->affiliate_referral?->user?->firstName . ' ' . $commissionLog->affiliate_referral?->user?->lastName;
                }
                return $commissionLog;
            });
            $environment = app()->environment();
            $commission_log = AffiliateCommissionLog::where('affiliate_id', $affiliate_user->id)->where('commission_status', "pending")->get();
            if (count($commission_log) > 0) {
                foreach ($commission_log as $commission) {
                    if (Carbon::parse($commission->credited_date) <= Carbon::now()) {
                        $commission->commission_status = "successful";
                        $commission->save();
                    }
                }
                $affiliate_detail->total_commission = AffiliateCommissionLog::where('affiliate_id', $affiliate_user->id)->sum('commission');
                $affiliate_detail->total_pending_commission = AffiliateCommissionLog::where('affiliate_id', $affiliate_user->id)->where('commission_status', "pending")->sum('commission');
                $affiliate_detail->current_balance = AffiliateCommissionLog::where('affiliate_id', $affiliate_user->id)->where('commission_status', "successful")->sum('commission');
                $affiliate_detail->save();
            }
            $detailCards = (array)[
                ['title' => 'Total', 'value' => $affiliate_detail->total_commission],
                ['title' => 'Pending', 'value' => $affiliate_detail->total_pending_commission],
                ['title' => 'Available For Payout', 'value' => $affiliate_detail->current_balance],
                ['title' => 'Withdrawn', 'value' => $affiliate_detail->total_withdrawal],
            ];
            $user_id = $affiliate_user?->accountUser()?->id;
            if($user_id){
                $affiliateReferral = AffiliateReferralLog::where('user_id',$user_id)->first();
            }else{
                $affiliateReferral = AffiliateReferralLog::where(['affiliate_id'=>$affiliate_user->id, 'referral_name'=>'root'])->first();
            }
            $levels = $affiliateReferral?->getSublines() ?? null;
            $uniqueClickLogs = AffiliateClickLog::where('affiliate_unique_id', $affiliate_user->affiliate_unique_id)->get();
            return Inertia::render(
                'hypershapes-affiliate/pages/Dashboard',
                array_merge(
                    [
                        'affiliateUser' => $affiliate_user,
                        'affiliateLog' => $affiliate_log,
                        'affiliateDetail' => $affiliate_detail,
                    ],
                    compact('affiliateCommissionLogs', 'affiliateReferrals', 'environment', 'detailCards', 'levels', 'uniqueClickLogs')
                )
            );
        } else {
            return redirect()->route('affiliate.login');
        }
    }
    public static function latestName($id)
    {
        $user = User::whereId($id)->first();
        return $user->firstName . ' ' . $user->lastName;
    }

    public function viewProfile()
    {

        $affiliateUser = $this->affiliateUser();
        $environment = app()->environment();

        return Inertia::render('hypershapes-affiliate/pages/Profile', compact('affiliateUser', 'environment'));
    }

    public function viewPayouts()
    {

        $affiliateUser = $this->affiliateUser();
        $environment = app()->environment();
        return Inertia::render(
            'hypershapes-affiliate/pages/Payouts',
            compact('affiliateUser', 'environment')
        );
    }


    public function addClickCount(Request $request)
    {

        $affiliate_detail = AffiliateDetail::where('affiliate_unique_link', $request->affiliate_id)->first();
        $affiliate_detail->total_click_for_link++;
        $affiliate_detail->save();
    }

    public function updateProfileSetting(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
        ],);
        $affiliate =  Auth::guard('affiliateusers')->user();
        $affiliate->first_name = $request->first_name;
        $affiliate->last_name = $request->last_name;
        $affiliate->address = $request->address;
        $affiliate->city = $request->city;
        $affiliate->zipcode = $request->zipcode;
        $affiliate->state = $request->state;
        $affiliate->country = $request->country;
        $affiliate->paypal_email = $request->paypal_email;
        $affiliate->save();

        return response()->json(['affiliate' => $affiliate]);
    }

    public function updatePayoutSetting(Request $request)
    {
        $request->validate([
            'paypal_email' => 'required|unique:affiliate_users',
            'paypal_email' => 'required|email',
        ], [
            'paypal_email.required' => 'Paypal email is required',
            'paypal_email.unique' => "This email has already been taken"
        ]);

        $user = Auth::guard('affiliateusers')->user();
        $user->paypal_email = $request->paypal_email;
        $user->save();
        return response()->json(['message' => 'Successfully update paypal email']);
    }

    public function updatePassword(UpdateAffiliatePassword $request)
    {
        $password = Hash::make($request->password);
        $user = Auth::guard('affiliateusers')->user();
        $user->password = $password;
        $user->save();
        return response()->json(['message' => 'Successfully updated password']);
    }

    public function checkCurrentBalance($commissionLog, $affiliate_detail)
    {
    }

    public function getCommissionDetail()
    {

        $affiliateUser = Auth::guard('affiliateusers')->user();
        $affiliateDetail = $affiliateUser->affiliateDetail;
        $detailArray = (array)[
            ['title' => 'Total Commissions', 'value' => $affiliateDetail->total_commission],
            ['title' => 'Pending Commissions', 'value' => $affiliateDetail->total_pending_commission],
            ['title' => 'Available Commissions For Payout', 'value' => $affiliateDetail->current_balance],
            ['title' => 'Withdrawn Commissions', 'value' => $affiliateDetail->total_withdrawal],
        ];
        return response($detailArray);
    }

    public function emailConfirmation()
    {
        $email =  Auth::guard('affiliateusers')->user()->email;
        return view('affiliate.auth.confirmEmail', compact('email'));
    }

    public function resendConfirmation()
    {
        $user =  Auth::guard('affiliateusers')->user();
        AffiliateMailsController::sendSignupEmail($user->first_name, $user->email, $user->verification_code);
        return redirect()->back()->with('status', 'We have resent the confirmation email!');
    }
}
