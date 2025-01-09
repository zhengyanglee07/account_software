<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EasyParcelShipmentParcel
 * @package App
 *
 * @mixin \Eloquent
 */
class EasyParcelShipmentParcel extends Model
{
    protected $fillable = [
        'easy_parcel_shipment_id',

        // after order payment is made
        'parcel_number',
        'ship_status',
        'tracking_url',
        'awb',
        'awb_id_link'
    ];
}
