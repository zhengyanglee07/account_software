<?php

namespace App\Http\Controllers;
use Auth;
use App\Account;
use App\PaymentAPI;
use Hashids\Hashids;
use App\AccountDomain;
use Illuminate\Http\Request;
use App\Traits\AuthAccountTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PaymentSettingController extends Controller
{
    use AuthAccountTrait;

    private function stripeSecretKey()
    {
//        return env('STRIPE_SECRET_KEY');
        return config('services.stripe.secret_key');
    }

    public function getPaymentSetting(Request $request)
    {
        $hashids = new Hashids('', 10);
        $accountId = $request->user()->currentAccountId;
        $paymentMethods = PaymentAPI::where('account_id', $accountId)->get();
        $env =  app()->environment();
        foreach($paymentMethods as $payment){
        //    if($payment->payment_methods==='cash on delivery') $payment->payment_methods = 'manual payment';
           $payment->urlId = $hashids->encode($payment->id);
        }
        if(Str::contains( url()->current(), 'onboarding')){
            setCookie('onboarding','true',0, "/",);
            return Inertia::render('onboarding/pages/PaymentMethodSelection', compact('paymentMethods','env'));
        }
        return Inertia::render('setting/pages/PaymentSettings', compact('paymentMethods','env'));
    }

    public function checkStripeOuthConnection()
    {
        $request = request();
        $error = (object)[
            'title' => $request->error,
            'message' => $request->error_description,
        ];

        $redirectUrl = '/payment/settings/new/stripe';
        if(isset($_COOKIE['onboarding'])){
            $redirectUrl = '/onboarding/payment/setup/new/stripe';
        }
        $error->title !== null
        ? setCookie('error',json_encode($error),'',$redirectUrl)
        : $this->connectSuccessful($request);
        return redirect($redirectUrl);
    }

    private function connectSuccessful($request)
    {
        \Stripe\Stripe::setApiKey($this->stripeSecretKey());

        $response = \Stripe\OAuth::token([
            'grant_type' => 'authorization_code',
            'code' => $request->code,
        ]);
        $paymentAPI = PaymentAPI::firstOrNew(
            [
                'account_id' => $request->user()->currentAccountId,
                'payment_methods' => 'stripe',
            ]
        );
        $paymentAPI->secret_key = $response->access_token;
        $paymentAPI->publishable_key = $response->stripe_publishable_key;
        $paymentAPI->refresh_token = $response->refresh_token;
        $paymentAPI->payment_account_id = $response->stripe_user_id;
        $paymentAPI->save();
    }

    private function getDomainUrl($type)
    {
        $accountDomain = AccountDomain::firstWhere(['account_id' => $this->getCurrentAccountId(),'type' => $type]);
        if($accountDomain !== null) {
            return "https://".$accountDomain->domain;
        }
        return '';
    }

    public function addPaymentSettings($urlId, $type, Request $request)
    {
        $id = $urlId;
        if($id!=='new'){
            $hashids = new Hashids('',10);
            $idArr = $hashids->decode($urlId);
            $id =  $idArr ? $idArr[0] : '';
        }
        $accountId = $request->user()->currentAccountId;
        if($type==='manual payment'){
            $paymentMethod = PaymentAPI::firstWhere(
                [
                    'account_id' => $accountId,
                    'id' => $id,
                ]
            );
        }else{
            $paymentMethod = PaymentAPI::firstWhere(
                [
                    'account_id' => $accountId,
                    'payment_methods' => $type,
                ]
            );
        }
        $this->accountName = '';
        if( $paymentMethod !== null && $type!=='manual payment'){
            if($paymentMethod->payment_account_id !== null){
                $this->accountName = $this->getStripeAccountName($paymentMethod->payment_account_id);
            }
        }
        $accountName = $this->accountName;
        $returnUrl = (object)[
            'onlineStore' => $this->getDomainUrl('online-store') . '/senangpay/redirect',
            'miniStore' => $this->getDomainUrl('mini-store') . '/senangpay/redirect',
        ];
        $domainUrl = (object)[
            'onlineStore' => $this->getDomainUrl('online-store'),
            'miniStore' => $this->getDomainUrl('mini-store'),
        ];
        $appUrl = config('app.url');
//        $stripeClientId = env('STRIPE_CLIENT_ID');
        $onboarding = Str::contains( url()->current(), 'onboarding');
        $stripeClientId = config('services.stripe.client_id');
        $pagePath = $onboarding ? 'onboarding/pages/PaymentSettingForm' : 'setting/pages/AddNewPaymentMethod';
        return Inertia::render($pagePath, compact('paymentMethod','type','returnUrl','stripeClientId','accountName','onboarding','domainUrl','appUrl'));
    }

    private function getStripeAccountName($account_id)
    {
        \Stripe\Stripe::setApiKey($this->stripeSecretKey());
        $account = \Stripe\Account::retrieve($account_id);
        return $account->settings->dashboard->display_name;
    }

    public function savePaymentSetting(request $request)
    {
        PaymentAPI::updateOrCreate(
            [
                'id' => $request->id,
            ],
            [
                'account_id' => $request->user()->currentAccountId,
                'payment_methods' => $request->payment_methods,
                'display_name' => $request->display_name,
                'display_name2' => $request->display_name2,
                'description' => $request->description,
                'enabled_at' => $request->enabled_at,
                'enable_fpx' => $request->enable_fpx,
                'publishable_key' => $request->publishable_key,
                'secret_key' => $request->secret_key,
            ]
        );
    }
}
