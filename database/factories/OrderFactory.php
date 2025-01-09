<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Order;

$factory->define(Order::class, function (Faker $faker) {
    return [
        'order_number' => '1000',
        'additional_status' => 'Closed',
        'fulfillment_status' => 'Unfulfilled',
        'payment_status' => 'Unpaid',
        'payment_process_status' => 'Pending',
        'payment_references' => \Str::random(12),
        'payment_method' => 'Manual Payment',
        'currency' => 'MYR',
        'exchange_rate' => 1,
        'subtotal' => 100,
        'shipping_method' => 'Pos Laju',
        'shipping_method_name' => 'EasyParcel',
        'shipping' => 7,
        'total' => 107,
        'shipping_name' => $faker->name('male'),
        'shipping_address' => $faker->streetAddress,
        'shipping_city' => $faker->city,
        'shipping_state' => 'Wilayah Persekutuan (Kuala Lumpur)',
        'shipping_zipcode' => 58000,
        'shipping_country' => 'Malaysia',
        'shipping_phoneNumber' => $faker->phoneNumber,
        'billing_name' => $faker->name('male'),
        'billing_address' => $faker->streetAddress,
        'billing_city' => $faker->city,
        'billing_state' => 'Wilayah Persekutuan (Kuala Lumpur)',
        'billing_zipcode' => 58000,
        'billing_country' => 'Malaysia',
        'billing_phoneNumber' => $faker->phoneNumber,
        'acquisition_channel' => 'Online Store',
        'reference_key' => \Str::random(12),
    ];
});
