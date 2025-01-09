<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\EasyParcel;
use Faker\Generator as Faker;
use App\Models\Model;

$factory->define(EasyParcel::class, function (Faker $faker) {
    return [
        'api_key' => "EP-ikxPCGBra",
        'easyparcel_selected' =>"1",
    ];
});
