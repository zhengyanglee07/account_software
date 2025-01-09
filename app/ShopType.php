<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\ShopType
 *
 * @property int $id
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Account[] $accounts
 * @property-read int|null $accounts_count
 * @method static Builder|ShopType newModelQuery()
 * @method static Builder|ShopType newQuery()
 * @method static Builder|ShopType query()
 * @method static Builder|ShopType whereCreatedAt($value)
 * @method static Builder|ShopType whereId($value)
 * @method static Builder|ShopType whereType($value)
 * @method static Builder|ShopType whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ShopType extends Model
{
    //
    protected $fillable = [
        'user_id',
        'shoptype_id',
        'api',
        'password',
        'domain'
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }
}
