<?php

namespace App\Http\Middleware;

use App\Account;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckUserShop
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
        $user = Auth::user();
        $accountRandomId = $request->route('shopId');
        $account = $user->accounts;
        $userShops = Account::where('id', $account[0]->id)->where('accountRandomId', $accountRandomId)->first();

        if ($userShops == "") {
            return redirect()->route('viewprofile', ['shopId' => session('accountRandomId')]);
        }

        return $next($request);
    }
}
