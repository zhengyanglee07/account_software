<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        if(Auth::check() && Auth::user()->hasVerifiedEmail()) {
            return redirect()->intended('/dashboard');
        }
        
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(RouteServiceProvider::DASHBOARD)
                    : Inertia::render('auth/pages/VerifyEmail', [
                        'status' => session('status'),
                        'email' => $request->user()->email ?? null
                    ]);
    }
}
