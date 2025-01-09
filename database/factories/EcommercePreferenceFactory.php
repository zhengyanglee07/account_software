<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\EcommercePreferences;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

$factory->define(EcommercePreferences::class, function (Faker $faker) {
    return [
        'is_fullname' => 1,
        'is_billingaddress' => 1,
        'is_companyname' => 1,
        'checkout_method' => 'email address',
        'register_methods' => 'email address',
        'has_affiliate_badge' => 1,
        'require_account' => 'hidden',
        'is_enable_store_pickup' => 1,
        'pickup_hour' => null,
        'pickup_disabled_date' => null,
        'pickup_pre_order_from' => 7,
        'pickup_is_limit_order' => 0,
        'pickup_is_preperation_time' => '0',
        'pickup_preperation_value' => 0,
        'delivery_hour_type' => 'default',
        'delivery_hour' => null,
        'delivery_disabled_date' => null,
        'delivery_pre_order_from' => 7,
        'delivery_is_limit_order' => 0,
        'delivery_is_preperation_time' => 0,
        'delivery_preperation_value' => 0,
        'updated_at' => Carbon::now(),
        'pickup_is_daily' => 1,
        'pickup_is_same_time' => 1,
        'delivery_is_daily' => 1,
        'delivery_is_same_time' => 1,
    ];
});
