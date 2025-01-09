    <?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Location;
use Faker\Generator as Faker;
use App\Models\Model;

$factory->define(Location::class, function (Faker $faker) {
    return [
        'name' => $faker->company, 
        'address1' => $faker->streetAddress,
        'address2' => null,
        'city' => $faker->city,
        'zip' => $faker->postcode,
        'country' => $faker->country,
        'state' => $faker->state,
        'phone_number' => $faker->phoneNumber,
        'email' => $faker->email,
    ];
});
