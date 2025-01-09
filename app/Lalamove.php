<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Lalamove
 *
 * @property string $api_key
 * @property string $api_secret
 *
 * @mixin \Eloquent
 */
class Lalamove extends Model
{
    protected $fillable = [
        'account_id',
        'api_key',
        'api_secret',
        'enable_car',
        'car_delivery_desc',
        'enable_bike',
        'bike_delivery_desc',
        'lalamove_selected',
    ];
}
