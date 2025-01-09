<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\AccountDomain;

$factory->define(AccountDomain::class, function (Faker $faker) {
    return [
        'domain' => $faker->domainName,
        'type_id' => 1,
        'type' => 'store',
        'is_subdomain' => 1,
        'is_verified' => 1,
        'is_affiliate_member_dashboard_domain' => 1,
    ];
});
