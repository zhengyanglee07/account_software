<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\AccountUser;

$factory->define(AccountUser::class, function (Faker $faker) {
    return [
        'role' => 'owner'
    ];
});
