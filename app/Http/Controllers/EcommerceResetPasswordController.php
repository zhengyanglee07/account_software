<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\Controller;
use App\Traits\EcommerceResetPasswordTraits;
use App\Traits\PublishedPageTrait;
use Illuminate\Support\Facades\Auth;
use App\Account;
use Inertia\Inertia;

class EcommerceResetPasswordController extends Controller
{
    use EcommerceResetPasswordTraits, PublishedPageTrait;

    protected $redirectTo = '/orders/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:ecommerceUsers');
    }

    public function broker()
    {
        return Password::broker('ecommerceUsers');
    }

    protected function guard()
    {
        return Auth::guard('ecommerceUsers');
    }

    public function showResetForm($domain, Request $request, $token = null)
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();
        $pageName = 'Reset Password Page';
        $account_id = $this->getAccountId();
        $companyLogo = Account::find($account_id)->company_logo;
        return Inertia::render(
            'customer-account/pages/ResetPassword',
            array_merge(
                $publishPageBaseData,
                [
                    'token' => $token,
                    'email' => $request->email,
                    'accountId' => $account_id,
                    'companyLogo' => $companyLogo,
                    'pageName' => $pageName
                ]
            )
        );
    }

    public function getResetData(Request $request, $token = null)
    {
        $pageName = 'Reset Password Page';
        $account_id = $this->getCurrentAccountId();
        $companyLogo = Account::find($account_id)->company_logo;
        return response()->json(
            [
                'token' => $token,
                'email' => $request->query('email'),
                'accountId' => $account_id,
                'companyLogo' => $companyLogo,
                'pageName' => $pageName
            ]
        );
    }
}
