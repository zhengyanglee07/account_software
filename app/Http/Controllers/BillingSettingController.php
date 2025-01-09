<?php

namespace App\Http\Controllers;
use Auth;
use App\Account;
use App\Subscription;
use App\SubscriptionPlan;
use App\CreditCardDetail;
use App\SubscriptionInvoice;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;
use Inertia\Inertia;

class BillingSettingController extends Controller
{
    private function current_accountId()
    {
        $user = Auth::user();
        return $user->currentAccountId;
    }

    private function getUserDetail ()
    {
        $user = Auth::user();
        $account = $user->currentAccount($this->current_accountId());
        $userDetail = (object)[
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'companyName' => $account->company,
            'address' => $account->address,
            'country' => $account->country,
            'state' => $account->state,
            'city' => $account->city,
            'zipCode' => $account->zip,
        ];
        return $userDetail;
    }

    public function showBillingSetting()
    {
        $userDetail = $this->getUserDetail();
        $subscriptionPlanId = Account::find($this->current_accountId())->subscription_plan_id;
        $subscription = Subscription::where('account_id',$this->current_accountId())->first();
        $planName = SubscriptionPlan::find($subscriptionPlanId)->plan;
        $creditCard = CreditCardDetail::where('account_id',$this->current_accountId())->first();
        $subscriptionInvoices = Account::find($this->current_accountId())->subscriptionInvoice;
        foreach($subscriptionInvoices as $subscriptionInvoice) {
            $subscriptionInvoice->date = explode(" ",$subscriptionInvoice->plan_start)[0];
        }
        return Inertia::render(
            'setting/pages/BillingSettings',
            [
                'subscription' => $subscription,
                'planName' => $planName,
                'creditCard' => $creditCard,
                'userDetail' => $userDetail,
                'subscriptionInvoices' => $subscriptionInvoices,
                'production' => app()->environment()
            ]
        );
    }
    public function saveBillingSetting(request $request)
    {
        $user = Auth::user();
        $account = $user->currentAccount($this->current_accountId());
        $user->update($request->only('firstName','lastName'));
        $account->update($request->all());
    }

    public function createSubscriptionInvoicePDF($refKey)
    {
        $subscriptionInvoice = SubscriptionInvoice::where('reference_key', $refKey)->first();
        $date = Carbon::createFromIsoFormat('YYYY-MM-DD HH:mm:ss',$subscriptionInvoice->plan_start)->isoFormat('YYYY_MM_DD');
        $title = $subscriptionInvoice->plan_name.'_'.$date;
        $pdf = PDF::loadView('emailTemplates.subscriptionInvoice.subscriptionInvoice',compact('subscriptionInvoice','title'));
        return $pdf->stream('subscriptionInvoice_' . $refKey . '.pdf');
    }
}
