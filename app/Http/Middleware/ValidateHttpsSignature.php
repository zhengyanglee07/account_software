<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class ValidateHttpsSignature
{
    protected $keyResolver;

    public function __construct()
    {
        $this->keyResolver = static function () {
            return App::make('config')->get('app.key');
        };
    }


    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Routing\Exceptions\InvalidSignatureException
     */
    public function handle($request, Closure $next)
    {
        if ($this->hasValidSignature($request)) {
            return $next($request);
        }

        throw new InvalidSignatureException;
    }

    /**
     * Determine if the given request has a valid signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  bool  $absolute
     * @param  array  $ignoreQuery
     * @return bool
     */
    public function hasValidSignature(Request $request, $absolute = true, array $ignoreQuery = [])
    {
        return $this->hasCorrectSignature($request, $absolute, $ignoreQuery)
            && $this->signatureHasNotExpired($request);
    }

    /**
     * Determine if the signature from the given request matches the URL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  bool  $absolute
     * @param  array  $ignoreQuery
     * @return bool
     */
    public function hasCorrectSignature(Request $request, $absolute = true, array $ignoreQuery = [])
    {
        $ignoreQuery[] = 'signature';

        $url = $absolute ? $request->url() : '/' . $request->path();

        $queryString = collect(explode('&', (string) $request->server->get('QUERY_STRING')))
            ->reject(fn ($parameter) => in_array(Str::before($parameter, '='), $ignoreQuery))
            ->join('&');

        $original = rtrim($url . '?' . $queryString, '?');

        $signature = hash_hmac('sha256', $original, call_user_func($this->keyResolver));

        return hash_equals($signature, (string) $request->query('signature', ''));
    }

    /**
     * Determine if the expires timestamp from the given request is not from the past.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function signatureHasNotExpired(Request $request)
    {
        $expires = $request->query('expires');

        return !($expires && Carbon::now()->getTimestamp() > $expires);
    }
}
