<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\ProcessedContact;

$factory->define(ProcessedContact::class, function (Faker $faker) {
    return [
        'contactRandomId' => \Str::random(12),
        'customer_id' => \Str::random(12),
        'fname' => $faker->firstName('male'),
        'lname' => $faker->lastName,
        'email' => $faker->email,
        'phone_number' => $faker->phoneNumber,
        'dateCreated' => now(),
        'acquisition_channel' => 'Online Store',
    ];
});
