<?php

namespace App\Http\Controllers\AffiliateMemberAuth;

use App\AccountDomain;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use App\Traits\PublishedPageTrait;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class ResetPasswordController extends Controller
{
    use ResetsPasswords, PublishedPageTrait;

    protected $redirectTo = '/affiliates/dashboard';

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param $domain
     * @param \Illuminate\Http\Request $request
     * @param string|null $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getResetData(Request $request, $token = null)
    {
        return response()->json(['token' => $token, 'email' => $request->query('email')]);
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::min(8)->letters()->numbers()],
        ];
    }

    /**
     * Get the password reset credentials from the request.
     *
     * Override to add account_id to credentials validation in reset() method
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request): array
    {
        return array_merge(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            ['account_id' => $this->getCurrentAccountId()]
        );
    }

    /**
     * Get the guard to be used during password reset.
     *
     */
    protected function guard()
    {
        return Auth::guard('affiliateMember');
    }

    public function domainInfo()
    {
        return AccountDomain::whereDomain($_SERVER['HTTP_HOST'])->first();
    }

    public function broker()
    {
        return Password::broker('affiliateMembers');
    }

    protected function resetPassword($user, $password)
    {
        $this->setUserPassword($user, $password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
    }


    public function reset(Request $request)
    {
        $request->validate($this->rules(), $this->validationErrorMessages());

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);
    }
}
