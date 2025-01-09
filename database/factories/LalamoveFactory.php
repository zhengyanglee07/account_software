<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Lalamove;
use Faker\Generator as Faker;
use App\Models\Model;

$factory->define(Lalamove::class, function (Faker $faker) {
    return [
        'api_key' => "pk_test_1acb00cb779f5e440df5c70e266a333e",
        'api_secret' =>"sk_test_6QtIgyUBD0jHjdBBfBOjvBhnYW6pGx8HJpuROIs/ji2Y9zEGrH7ydPOypj+FbH2P",
        'enable_car' =>"1",
        'car_delivery_desc' =>"Same-Day Delivery",
        'enable_bike' =>"1",
        'bike_delivery_desc' =>"2-hour Express Delivery",
        'lalamove_selected' =>"1",
    ];
});
