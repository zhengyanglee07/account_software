<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Closure;

class CheckPermissions
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
        // $user = Auth::user();
        // dd($user);

        // if(Gate::allows('add-landing-page',['total_landingpage','add-landing-page'])){

        //     return response()->json(['add_landingpage'=> false]);
        // }

        return $next($request);
    }
}
