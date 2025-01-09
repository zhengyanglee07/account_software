<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MJML endpoint
    |--------------------------------------------------------------------------
    |
    | Custom field in mail.php. Used to indicate service endpoint for
    | MJML. Default to localhost:8085/mjml
    |
    */
    'mjml_endpoint' => env('MJML_ENDPOINT', 'localhost:8085/mjml'),
    'mjml_application_id' => env('MJML_APPLICATION_ID', null),
    'mjml_secret_key' => env('MJML_SECRET_KEY', null),

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send any email
    | messages sent by your application. Alternative mailers may be setup
    | and used as needed; however, this mailer will be used by default.
    |
    */

    // set default as SMTP to ease local development
    // IMPORTANT: remember to add 'ses-trans' to .env in production
    'default' => env('MAIL_MAILER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers to be used while
    | sending an e-mail. You will specify which one you are using for your
    | mailers below. You are free to add additional mailers as required.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses",
    |            "postmark", "log", "array"
    |
    */

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'auth_mode' => null,
        ],

        // For SES usage in transactional email only
        // Default to region us-west-2
        'ses-trans' => [
//            'transport' => 'ses',
            'transport' => 'smtp',
            'host' => env('SES_TRANS_MAIL_HOST', null),
            'port' => env('SES_TRANS_MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('SES_TRANS_MAIL_USERNAME'),
            'password' => env('SES_TRANS_MAIL_PASSWORD'),
            'region' => env('SES_TRANS_MAIL_REGION', 'us-west-2'),
            'timeout' => null,
            'auth_mode' => null,
            'version' => 'latest',
            'service' => 'email',
        ],

        // For SES usage in marketing email
        // Default to region ap-southeast-1
        'ses-markt' => [
//            'transport' => 'ses',
            'transport' => 'smtp',
            'host' => env('SES_MARKT_MAIL_HOST', null),
            'port' => env('SES_MARKT_MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('SES_MARKT_MAIL_USERNAME'),
            'password' => env('SES_MARKT_MAIL_PASSWORD'),
            'region' => env('SES_MARKT_MAIL_REGION'),
            'timeout' => null,
            'auth_mode' => null,
            'version' => 'latest',
            'service' => 'email',
        ],

        'mailgun' => [
            'transport' => 'mailgun',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => '/usr/sbin/sendmail -bs',
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all e-mails sent by your application to be sent from
    | the same address. Here, you may specify a name and address that is
    | used globally for all e-mails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'mail@myhypershapes.com'),
        'name' => env('MAIL_FROM_NAME', 'Hypershapes'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    |
    | If you are using Markdown based email rendering, you may configure your
    | theme and component paths here, allowing you to customize the design
    | of the emails. Or, you may simply stick with the Laravel defaults!
    |
    */

    'markdown' => [
        'theme' => 'default',

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],
    'stream' => [
        'ssl' => [
           'allow_self_signed' => true,
           'verify_peer' => false,
           'verify_peer_name' => false,
        ],
     ],
];
