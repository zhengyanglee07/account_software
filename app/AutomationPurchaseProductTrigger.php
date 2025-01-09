<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AutomationPurchaseProductTrigger
 *
 * @property int $id
 * @property int $automation_trigger_id
 * @property int $users_product_id
 * @property-read UsersProduct $usersProduct
 * @mixin \Eloquent
 */
class AutomationPurchaseProductTrigger extends Model
{
    protected $fillable = [
        'automation_trigger_id',
        'users_product_id'
    ];

    protected $appends = ['description'];

    public function usersProduct(): BelongsTo
    {
        return $this->belongsTo(UsersProduct::class);
    }

    /**
     * @return string
     */
    public function getDescriptionAttribute(): string
    {
        $usersProduct = $this->usersProduct;

        $productName = !$usersProduct
            ? 'Any product'
            : (optional($usersProduct)->productTitle ?? '[empty name]');

        return "Purchase \"$productName\"";
    }
}
