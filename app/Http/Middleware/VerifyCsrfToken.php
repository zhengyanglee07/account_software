<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        // Note: This is just for the convenience of testing with POSTMAN only,
        // and the routes shouldn't be accessed by users
        'andy/*',
        '/api/v1/users/accounts/basic',
        '/ipay88/*',

        //'https://staging.hypershapes.com/getBillingWebhook',
	//'https://my.hypershapes.com/getBillingWebhook',
	'getBillingWebhook',
	'product/subscrption/webhook',
        'emails/sns'
        // 'https://restcountries.eu/rest/v2/all',
    ];
}
