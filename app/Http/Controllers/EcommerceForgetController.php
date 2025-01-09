<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use App\Account;
use Auth;
use App\Traits\PublishedPageTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\EcommerceAccount;
use App\Traits\AuthAccountTrait;
use Inertia\Inertia;

class EcommerceForgetController extends Controller
{

    use SendsPasswordResetEmails, PublishedPageTrait, AuthAccountTrait;

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('guest:ecommerceUsers');
    }

    public function show()
    {
        if (Auth::guard('ecommerceUsers')->check() && Auth::guard('ecommerceUsers')->user()->hasVerifiedEmail()) {
            return redirect()->intended('/orders/dashboard');
        }

        $publishPageBaseData = $this->getPublishedPageBaseData();
        $pageName = 'Forgot Password Page';
        $account_id = $this->getAccountId();
        $companyLogo = Account::find($account_id)->company_logo;

        return Inertia::render('customer-account/pages/ForgetPassword', array_merge(
            $publishPageBaseData,
            [
                'account_id' => $account_id,
                'companyLogo' => $companyLogo,
                'pageName' => $pageName,
                'status' => session('status')
            ]
        ));
    }

    public function getForgotData()
    {
        if (Auth::guard('ecommerceUsers')->check() && Auth::guard('ecommerceUsers')->user()->hasVerifiedEmail()) {
            return abort(302, '/customer-account/orders/dashboard');
        }

        $publishPageBaseData = $this->getPublishedPageBaseData();
        $pageName = 'Forgot Password Page';
        $account_id = $this->getCurrentAccountId();
        $companyLogo = Account::find($account_id)->company_logo;

        return response()->json(array_merge(
            $publishPageBaseData,
            [
                'account_id' => $account_id,
                'companyLogo' => $companyLogo,
                'pageName' => $pageName,
                'status' => session('status')
            ]
        ));
    }


    public function broker()
    {
        return Password::broker('ecommerceUsers');
    }

    public function store(Request $request)
    {
        $accountId = $this->getCurrentAccountId();

        $request->validate([
            'email' => 'required|email',
        ]);
        $credentials = array_merge($request->only('email'), ['account_id' => $accountId]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = $this->broker()->sendResetLink(
            $credentials
        );

        if ($status == Password::RESET_LINK_SENT || $status == Password::INVALID_USER) {
            abort(302, json_encode(
                [
                    'url' => '/customer-account/login',
                    'status' => [
                        'message' => __($status),
                        'result' => $status == Password::RESET_LINK_SENT ? 'success' : 'failed'
                    ]
                ]
            ));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
