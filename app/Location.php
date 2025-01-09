<?php

namespace App;

use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Location
 * @package App
 *
 * @property string $name
 * @property string $address1
 * @property string $address2
 * @property string $city
 * @property int zip
 * @property string country
 * @property string state
 * @property string $phone_number
 * @property string $email
 * @property string $displayAddr
 *
 * @mixin \Eloquent
 */
class Location extends Model
{
    use BelongsToAccount;

    protected $fillable = [
        'account_id',
        'name',
        'address1',
        'address2',
        'city',
        'zip',
        'country',
        'state',
        'phone_number',
        'email'
    ];
    protected $appends = ['display_addr'];

    // if you need this on frontend you can $appends it
    public function getDisplayAddrAttribute(): string
    {
        return $this->address1
            . ' ' . $this->address2
            . ' ' . $this->city
            . ' ' . $this->zip
            . ' ' . $this->state
            . ' ' . $this->country;
    }
}
