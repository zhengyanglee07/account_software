<?php

namespace App;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Eloquent;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * App\Order
 *
 * @property int $account_id
 * @property string $shipping_name
 * @property string $shipping_address
 * @property string $shipping_city
 * @property string $shipping_state
 * @property string $shipping_zipcode
 * @property string $shipping_country
 * @property string $shipping_phoneNumber
 * @property string $displayShippingAddr
 * @property int $processed_contact_id
 * @property-read Collection $orderDetails
 * @property-read ProcessedContact $processedContact
 * @property-read \App\EasyParcelShipment[] $easyParcelShipments
 * @property-read LalamoveQuotation[] $lalamoveQuotations
 * @property-read LalamoveDeliveryOrder[] $lalamoveDeliveryOrders
 * @mixin Eloquent
 */
class Order extends Model
{
    use SoftDeletes;
    protected $with = ['orderDetails', 'orderDiscount'];
    protected $guarded = ['id'];

    public function processedContact(): BelongsTo
    {
        return $this->belongsTo(ProcessedContact::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'order_id')->withTrashed();
    }
    /**
     * Use seller here instead of account for better understanding.
     * Seller = account for an order
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function sellerEmail()
    {
        $orderAccount = $this->seller;

        return $orderAccount ? $orderAccount->user()->first()->email : '';
    }

    // if you need this on frontend you can $appends it
    public function getDisplayShippingAddrAttribute(): string
    {
        return $this->shipping_address
            . ' ' . $this->shipping_city
            . ' ' . $this->shipping_zipcode
            . ' ' . $this->shipping_state
            . ' ' . $this->shipping_country;
    }

    public function orderDiscount()
    {
        return $this->hasMany(Models\Orders\OrderDiscount::class);
    }

    public function easyParcelShipments(): HasMany
    {
        return $this->hasMany(EasyParcelShipment::class);
    }

    public function easyParcelShipmentParcels(): HasManyThrough
    {
        return $this->hasManyThrough(
            EasyParcelShipmentParcel::class,
            EasyParcelShipment::class
        );
    }

    public function easyParcelFulfillments(): HasManyThrough
    {
        return $this->hasManyThrough(
            EasyParcelFulfillment::class,
            EasyParcelShipment::class
        );
    }

    public function lalamoveQuotations(): HasMany
    {
        return $this->hasMany(LalamoveQuotation::class);
    }

    public function lalamoveDeliveryOrders(): HasManyThrough
    {
        return $this->hasManyThrough(LalamoveDeliveryOrder::class, LalamoveQuotation::class);
    }

    public function delyvaQuotations(): HasMany
    {
        return $this->hasMany(DelyvaQuotation::class);
    }

    public function delyvaDeliveryOrders()
    {
        return $this->hasManyThrough(DelyvaDeliveryOrder::class, DelyvaQuotation::class);
    }

    public function referralInvitee()
    {
        return $this->belongsTo(ReferralInviteeOrder::class);
    }

    public function nextOrder()
    {
        return Order::where('account_id', $this->account_id)->where('id', '>', $this->id)
            ->where(function ($query) {
                $query->where('payment_process_status', 'Success')->orWhereNull('payment_process_status');
            })
            ->orderBy('id', 'asc')->first();
    }

    public function previousOrder()
    {
        return Order::where('account_id', $this->account_id)->where('id', '<', $this->id)
            ->where(function ($query) {
                $query->where('payment_process_status', 'Success')->orWhereNull('payment_process_status');
            })
            ->orderBy('id', 'desc')->first();
    }
}
