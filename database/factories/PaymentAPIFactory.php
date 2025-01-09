<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\PaymentAPI;

$factory->define(PaymentAPI::class, function (Faker $faker) {
    return [
        'enabled_at' => 1,
    ];
});
