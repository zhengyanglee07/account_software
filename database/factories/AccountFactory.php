<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */


use Faker\Generator as Faker;
use App\Account;

$factory->define(Account::class, function (Faker $faker) {
    return [
        'accountRandomId' => \Str::random(12),
        'subscription_plan_id' => 1,
        'domain' => $faker->domainName,
        'company' => $faker->company,
        'address' => $faker->streetAddress,
        'city' => $faker->city,
        'state' => 'Wilayah Persekutuan (Kuala Lumpur)',
        'zip' => 58000,
        'timeZone' => 'Asia/Kuala_Lumpur',
        'country' => 'Malaysia',
        'currency' => "MYR",
        'industry' => 'Sell Products',
        'status' => 'pending',
        'subscription_status' => 'active',
        'terminate_cycle' => 'end of the billing cycle'
    ];
});