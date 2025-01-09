<?php

namespace App\Http\Controllers\AffiliateMemberAuth;

use App\Account;
use App\Http\Controllers\Controller;
use App\Mail\AffiliateMemberSignupEmail;
use App\Services\AffiliateEmailService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Mail;
use App\Traits\PublishedPageTrait;

class VerificationController extends Controller
{
    use PublishedPageTrait;
    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = '/affiliates/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function checkAuth()
    {
        $affiliateMember = Auth::guard('affiliateMember');
        return response()->json([
            'isAuthenticated' => $affiliateMember->check(),
            'isVerified' => !empty($affiliateMember->user()?->email_verified_at ?? null)
        ]);
    }

    public function getEmailConfirmationData()
    {
        $user = Auth::guard('affiliateMember')->user();

        $account = Account::find($user->account_id);
        $email = $user->email;

        return response()->json([
            'isVerified' => $user->email_verified_at,
            'account' => $account ?? null,
            'email' => $email ?? null,
        ]);
    }

    public function verify(Request $request)
    {
        $request->user = $request->user() ??  Auth::guard('affiliateMember')->user();
        if (!isset($request->user) ||  !hash_equals((string) $request->query('id'), (string) $request->user->getKey())) {
            throw new AuthorizationException;
        }

        if (!hash_equals((string) $request->query('hash'), sha1($request->user->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        if ($request->user->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? new JsonResponse([], 204)
                : redirect($this->redirectTo);
        }

        $user = $request->user;
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
        $account = Account::find($user->account_id);
        if ($account?->affiliateMemberSettings?->auto_approve_affiliate) {
            Mail::to($user->email)->send(new \App\Mail\AffiliateMemberWelcomeEmail($user));
        }


        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect($this->redirectTo)->with('verified', true);
    }

    public function resend(Request $request)
    {

        $user = Auth::guard('affiliateMember')->user();
        if ($user->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? new JsonResponse([], 204)
                : redirect('/affiliates/dashboard');
        }

        $url =  AffiliateEmailService::getUrl('/affiliates/email/verify', [
            'domain' => $_SERVER['HTTP_HOST'],
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        Mail::to($user->email)->send(new AffiliateMemberSignupEmail($user, $url));

        return $request->wantsJson()
            ? new JsonResponse([], 202)
            : back()->with('resent', true);
    }
}
