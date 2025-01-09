<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],


    'ses' => [
        'key' => env('MAIL_USERNAME'),
        'secret' => env('MAIL_PASSWORD'),
        'domain' => 'hypershapes.com',
        'region' => env('MAIL_REGION')
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],

        // for oauth
        // ignore 'secret' above if appropriate, secret here is prioritized
        'client_id' => env('STRIPE_CLIENT_ID'),
        'secret_key' => env('STRIPE_SECRET_KEY')
    ],

    'facebook' => [
//        'client_id' => '595264144545424',
//        'client_secret' => 'b63b66282b2b40a34f0afe9378766cd3',
//        'redirect' => rtrim(env('APP_URL'), '/') . '/callback/facebook',
        'app_id' => env('FACEBOOK_APP_ID', null),
        'app_secret' => env('FACEBOOK_APP_SECRET', null)
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT'),
    ],

];
