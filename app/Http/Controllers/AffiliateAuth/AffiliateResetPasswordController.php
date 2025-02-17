<?php

namespace App\Http\Controllers\AffiliateAuth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
class AffiliateResetPasswordController extends Controller
{
       /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:affiliateusers');
    }

    public function showResetForm(Request $request, $token = null)
    {
        return Inertia::render('hypershapes-affiliate/pages/ResetPassword')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }


    public function broker()
    {
        return Password::broker('affiliateusers');
    }

    protected function guard()
    {
        return Auth::guard('affiliateusers');
    }





}
