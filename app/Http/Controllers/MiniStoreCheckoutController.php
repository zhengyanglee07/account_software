<?php

namespace App\Http\Controllers;

use Auth;
use App\Currency;
use App\Cashback;
use App\AccountDomain;
use App\EcommercePreferences;
use App\Order;
use App\Services\SegmentService;
use App\Account;
use App\Location;
use App\Http\Controllers\Controller;
class MiniStoreCheckoutController extends Controller
{
    private function domainInfo()
    {
        return AccountDomain::whereDomain($_SERVER['HTTP_HOST'])->first();
	}

    private function getAccountId()
    {
        return $this->domainInfo() != null
        ? $this->domainInfo()->account_id
        : Auth::user()->currentAccountId;
    }
    private function getStorePreferences()
	{
		return EcommercePreferences::firstWhere('account_id', $this->getAccountId());
	}
    private function getDefaultCurrency(){
        $defaultCurrency = Currency::where('account_id',$this->getAccountId())->where('isDefault','1')->first()->currency;
        return $defaultCurrency;
    }
    private function getCashback()
    {
        $segmentService = new SegmentService();
        $cashbacks = Cashback::where('account_id', $this->getAccountId())
        ->with('segments')
        ->orderBy('cashback_amount', 'DESC')
        ->orderBy('expire_date', 'DESC')->get();
        $cashbackDetails = array();

        foreach ($cashbacks as $cashback) {
            $segments = $cashback->segments;
            $contactIds = array();
            foreach ($segments as $segment) {
                // $condition = json_decode($segment->conditions, true);
                // $contactIds = array_unique(array_merge($contactIds, $segmentService->filterContacts($condition)));
                $contactIds = $segment->contacts(true);
            }
            $contactIds = array_values($contactIds);
            $cashback['contactIds'] = $contactIds;
        }
    }
    public function getMiniStoreCheckout($domain)
    {
        $domainName = $domain;
        $cashback = $this->getCashback() ?? [];
        $accountId = $this->getAccountId();
        $pageName = 'Mini Store Checkout Page';
        $user = Auth::guard('ecommerceUsers')->user();
        $defaultCurrency = $this->getDefaultCurrency();
        $storePreferences = $this->getStorePreferences();
        $order = Order::where('account_id',$this->getAccountId())
        ->where('delivery_hour_type','custom')
        ->get();
        $account = Account::find($accountId);
        $location = $account->location;
        return view('miniStore.miniStoreCheckoutPage',compact(
            'order',
            'user',
            'cashback',
            'pageName',
            'accountId',
            'domainName',
            'defaultCurrency',
            'storePreferences',
            'location'
        ));
    }
}
