<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\AffiliateUser;
use App\User;
use App\Mail\AffiliateSignupEmail;
use App\Http\Controllers\AffiliateMailsController;

class AffiliateAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $user = Auth::guard('affiliateusers')->user();
        $hypershapesUser = null;
        if($user) $hypershapesUser = User::whereEmail($user->email)->first();
        $verifiedInHypershapes = false;
        if($hypershapesUser) $verifiedInHypershapes = $hypershapesUser->email_verified_at!==null;
        if($user == null){
            return redirect('/login');
        }
        // dd($verifiedInHypershapes);
        if(!$verifiedInHypershapes && !$user->is_verified){
            AffiliateMailsController::sendSignupEmail($user->first_name,$user->email,$user->verification_code);
            return  redirect('/verify');
        }
        // if(!$user->is_verified){
        //     $user =  Auth::guard('affiliateusers')->user();
        //     AffiliateMailsController::sendSignupEmail($user->first_name,$user->email,$user->verification_code);
        //     return  redirect('/confirm/email');
        // }

        return $next($request);
    }
}
