<?php

namespace App\Http\Middleware;

use App\Repository\CheckoutRepository;
use App\Services\RedisService;
use Closure;
use Illuminate\Support\Facades\Log;

class CheckoutMiddleware
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
        // Update client session if changed
        $oldSessionId = $request->cookie('clientId');
        $newSessionId = session()->getId();
        if($oldSessionId !== $newSessionId) RedisService::renameKey($oldSessionId);

        new CheckoutRepository();
        return $next($request);
    }
}
