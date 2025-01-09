<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Currency;

$factory->define(Currency::class, function (Faker $faker) {
    return [
        'currency' => 'MYR',
        'exchangeRate' => '1',
        'suggestRate' => 1,
        'isDefault' => 1,
        'rounding' => 0,
        'decimal_places' => 2,
        'separator_type' => ',',
        'prefix' => 'RM',
    ];
});
