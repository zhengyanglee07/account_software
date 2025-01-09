<?php

namespace App\Http\Controllers\AffiliateMemberAuth;

use App\Account;
use App\AccountDomain;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use App\Traits\PublishedPageTrait;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    use PublishedPageTrait;
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */
    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:affiliateMember');
    }

    public function getResetData()
    {
        $account = Account::findOrFail($this->getCurrentAccountId());
        $status = session('status');
        return response()->json([
            'account' => $account ?? null,
            'status' => $status ?? null,
        ]);
    }

    /**
     * Get the needed authentication credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return array_merge(
            $request->only('email'),
            ['account_id' => $this->getCurrentAccountId()]
        );
    }


    public function domainInfo()
    {
        return AccountDomain::whereDomain($_SERVER['HTTP_HOST'])->first();
    }

    public function broker()
    {
        return Password::broker('affiliateMembers');
    }

    public function sendResetLink(Request $request)
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
                    'url' => '/affiliates/login',
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
