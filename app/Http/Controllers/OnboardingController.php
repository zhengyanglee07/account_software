<?php

namespace App\Http\Controllers;

use App\AccountDomain;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OnboardingController extends Controller
{
    public function onboardingWelcome()
	{
		$saleChannel =  Auth::user()->currentAccount()->saleChannelsType();
		$domain = AccountDomain::where([
			'account_id' => Auth::user()->currentAccountId,
			'is_subdomain' => 1
		])->first();

		return Inertia::render('onboarding/pages/Welcome', compact('domain','saleChannel'));
	}
}
