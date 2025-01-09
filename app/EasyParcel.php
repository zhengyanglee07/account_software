<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EasyParcel
 * @package App
 *
 * @property int $account_id
 * @property int $api_key
 *
 * @mixin \Eloquent
 */
class EasyParcel extends Model
{
    protected $fillable = [
        'account_id',
        'api_key',
        'easyparcel_selected',
    ];
}
