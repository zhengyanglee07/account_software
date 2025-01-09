<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class EcommerceVisitor
 *
 * @mixin \Eloquent
 */
class EcommerceVisitor extends Model
{
    protected $fillable =
    [
        'account_id',
        'processed_contact_id',
        'is_completed',
        'sales_channel',
        'reference_key',
        'funnel_id',
    ];

    public function abandonedCart(): HasOne
    {
        return $this->hasOne(EcommerceAbandonedCart::class, 'visitor_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(EcommerceTrackingLog::class, 'visitor_id');
    }
}
