<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Validation\ValidationException;
use App\AccountDomain;
use App\Account;

use App\EcommerceVisitor;
use App\EcommerceTrackingLog;
use App\EcommerceAbandonedCart;
use App\EcommercePage;

use App\Traits\AuthAccountTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Repository\Checkout\FormDetail;
use App\Services\RedisService;
use Illuminate\Support\Facades\Route;
use App\Traits\PublishedPageTrait;
use Inertia\Inertia;

class EcommerceLoginController extends Controller
{

    use AuthenticatesUsers, AuthAccountTrait, PublishedPageTrait;

    protected $redirectTo = '/orders/dashboard';


    public function __construct()
    {
        $this->middleware('guest:ecommerceUsers')->except('logout');
        // $this->middleware('affiliateusers')->except('logout');
    }

    private function domainInfo()
    {
        return AccountDomain::whereDomain($_SERVER['HTTP_HOST'])->first();
    }

    protected function guard()
    {
        return Auth::guard('ecommerceUsers');
    }

    public function username()
    {
        return 'email';
    }

    public function show()
    {
        if ($this->guard()->check() && $this->guard()->user()->hasVerifiedEmail()) {
            return redirect()->intended('/orders/dashboard');
        }

        $publishPageBaseData = $this->getPublishedPageBaseData();
        $companyLogo = Account::find($this->getCurrentAccountId())->company_logo;

        return Inertia::render('customer-account/pages/Login', array_merge(
            $publishPageBaseData,
            [
                'companyLogo' => $companyLogo,
                'pageName' => "Login Page",
                'status' => session('status'),
            ]
        ));
    }

    public function login(request $request)
    {
        $oldSessionId = session()->getId();
        $this->validateLogin($request);
        if ($this->authenticate($request)) {
            $request->session()->regenerate();
            // Important, always update redis key after session updated
            RedisService::renameKey($oldSessionId);

            // app('App\Http\Controllers\OnlineStoreTrackingController')->setVisitorEmail($request->email);
            if (!$request->isManuallyLogin) {
                return response()->json([
                    'redirect' => $this->redirectPath(),
                    'customerInfo' => $this->guard()->user()->processedContact ?? null,
                ]);
            } else {
                $ecommerceUser = Auth::guard('ecommerceUsers')->user();
                $formDetails = (object)[
                    'processedContact' => $ecommerceUser->processedContact,
                    'address' => $ecommerceUser->addressBook[0],
                ];
                $customerInfo = (new FormDetail())->customerInfo ?? null;
                return response()->json(['status' => 'success', 'user' => $ecommerceUser, 'formDetails' => $formDetails, 'customerInfo' => $customerInfo]);
            }
        } else {
            if ($request->isManuallyLogin) {
                return response()->json(['status' => 'failed']);
            }
            throw ValidationException::withMessages([
                $this->username() => [trans('auth.failed')],
            ]);
        }
    }

    public function logout(request $request)
    {
        $oldSessionId = session()->getId();

        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Important, always update redis key with latest session id after logout
        RedisService::renameKey($oldSessionId);

        $redirectUrl = $request->query('redirectUrl');
        if ($redirectUrl) return response($redirectUrl);
        return redirect('/login');
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    public function authenticate(Request $request)
    {
        $credentials = array_merge($request->only($this->username(), 'password'), ['account_id' => $this->getCurrentAccountId()]);
        return $this->guard()->attempt($credentials, $request->filled('remember'));
    }
    public function getRandomId($table, $type)
    {
        $condition = true;
        do {
            return $randomId = random_int(100000000001, 999999999999);
            $condition = DB::table($table)->where($type, $randomId)->exists();
        } while ($condition);
    }
}
