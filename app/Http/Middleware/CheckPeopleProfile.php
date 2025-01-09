<?php

namespace App\Http\Middleware;

use App\ProcessedContact;
use Closure;

class CheckPeopleProfile
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

        $id = $request->route('id');
        $people = ProcessedContact::where('account_id',session('id'))->where('contactRandomId',$id)->first();

// dd($people);
        if($people==""){
            return redirect()-> route('viewprofile',['shopId'=>session('accountRandomId')]);

        }else{
            return $next($request);

        }


    }
}
