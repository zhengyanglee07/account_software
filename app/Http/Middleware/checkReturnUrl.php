<?php

namespace App\Http\Middleware;

use Closure;

class checkReturnUrl
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
        if(isset($_COOKIE['redirectUrl'])){
            $redirectUrl = $_COOKIE['redirectUrl'];
            setcookie("redirectUrl", "", time() - 3600);
            return redirect($redirectUrl);
        }
        return $next($request);
    }
}
