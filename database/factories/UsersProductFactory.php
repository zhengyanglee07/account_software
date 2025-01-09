<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\UsersProduct;
use Illuminate\Support\Str;

$factory->define(UsersProduct::class, function (Faker $faker) {
    $productTitle = $faker->name;
    return [
        'status' => 'active',
        'title' => $productTitle,
        'path' => $productTitle,
        'description' => $faker->text,
        'image_url' => $faker->imageUrl(100, 100, 'cats'),
        'image_collection' => [$faker->imageUrl(100, 100, 'cats')],
        'price' => (string)$faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 20),
        'compare_price' =>  (string)$faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = NULL),
        'type' => 'virtual',
        'weight' => 0.00,
        'sku' =>  null,
        'is_selling' => true,
        'quantity' =>  0,
        'is_taxable' => true,
        'reference_key' => Str::random(10),
    ];
});
