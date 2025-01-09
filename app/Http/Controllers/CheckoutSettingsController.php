<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\EcommercePreferences;
use Auth;
use Inertia\Inertia;
class CheckoutSettingsController extends Controller
{
    private function current_accountId()
	{
        return Auth::user()->currentAccountId;
    }
    private function getEcommercePreferences()
    {
        return EcommercePreferences::firstWhere(['account_id' => $this->current_accountId()]);
    }
    public function getCheckoutSetting()
    {
        $preference = $this->getEcommercePreferences();
        return Inertia::render('setting/pages/CheckoutSettings', compact('preference'));
    }
    public function saveCheckoutSettings(request $request)
    {
        $preference = $this->getEcommercePreferences();
        $preference->update($request->only(
            'is_billingaddress',
            'is_companyname',
            'is_fullname',
            'require_account',
            'checkout_method'
        ));
        return response()->json($preference);
    }
}
