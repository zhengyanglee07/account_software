<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\ProcessedAddress
 *
 * @property int $id
 * @property int $processed_contact_id
 * @property string|null $company
 * @property string|null $phone
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property string|null $zip
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ProcessedAddress newModelQuery()
 * @method static Builder|ProcessedAddress newQuery()
 * @method static Builder|ProcessedAddress query()
 * @method static Builder|ProcessedAddress whereAddress1($value)
 * @method static Builder|ProcessedAddress whereAddress2($value)
 * @method static Builder|ProcessedAddress whereCity($value)
 * @method static Builder|ProcessedAddress whereState($value)
 * @method static Builder|ProcessedAddress whereCompany($value)
 * @method static Builder|ProcessedAddress whereCountry($value)
 * @method static Builder|ProcessedAddress whereCreatedAt($value)
 * @method static Builder|ProcessedAddress whereId($value)
 * @method static Builder|ProcessedAddress wherePhone($value)
 * @method static Builder|ProcessedAddress whereProcessedContactId($value)
 * @method static Builder|ProcessedAddress whereUpdatedAt($value)
 * @method static Builder|ProcessedAddress whereZip($value)
 * @mixin Eloquent
 */
class ProcessedAddress extends Model
{
    //
    protected $fillable = [
        'processed_contact_id',
        'company',
        'address1',
        'address2',
        'city',
        'state',
        'country',
        'zip',
        'phone',
    ];
}
