<?php

namespace App\Http\Controllers\AffiliateMemberAuth;

use App\AffiliateMemberAccount;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\PublishedPageTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use  PublishedPageTrait;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/affiliates/dashboard';


    public function login(Request $request)
    {
        $memberAccount = AffiliateMemberAccount::firstWhere(['account_id' => $this->getCurrentAccountId(), 'email' => $request->email]);
        if (!Hash::check($request->password, $memberAccount?->password)) {
            throw ValidationException::withMessages([
                'email' => ['The email or password you entered is incorrect.'],
            ]);
        }

        return $request->wantsJson()
            ? new JsonResponse([
                'token' => $memberAccount->createToken('hypershapes_affiliate_member')->plainTextToken,
            ], 201)
            : redirect($this->redirectPath());
    }

    /**
     * Log the user out of the application.
     * Overrides trait logout to provide custom redirect
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect("/affiliates/login");
    }
}
