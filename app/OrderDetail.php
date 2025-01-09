<?php

namespace App;

use App\Models\AccessList;
use App\Models\CourseStudent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class OrderDetail
 * @package App
 *
 * @property-read Order $order
 *
 * @mixin \Eloquent
 */
class OrderDetail extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = ['max', 'productType'];


    public function usersProduct(): BelongsTo
    {
        return $this->belongsTo(UsersProduct::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class)->with('processedContact');
    }

    public function easyParcelFulfillment(): HasOne
    {
        return $this->hasOne(EasyParcelFulfillment::class);
    }

    public function getProductTypeAttribute()
    {
        return $this->usersProduct?->type;
    }
    /**
     * Used in (partial) fulfillment
     */
    public function getMaxAttribute()
    {
        return $this->quantity;
    }

    public static function boot()
    {
        parent::boot();

        function addStudentOrAccessList($orderDetail)
        {
            if ($orderDetail->fulfillment_status !== 'Fulfilled') return;
            $product = $orderDetail->usersProduct;
            if ($product->type === 'physical') return;
            $model = ($product->type === 'course' ? CourseStudent::class : AccessList::class)::withTrashed()
                ->firstOrNew([
                    'product_id' => $product->id,
                    'processed_contact_id' => $orderDetail->order->processed_contact_id,
                ]);
            if (!$model->is_active) {
                $model->is_active = 1;
                $model->join_at = date('Y-m-d H:i:s');
            }
            $model->deleted_at = null;
            $model->save();
        }

        static::created(function ($orderDetail) {
            addStudentOrAccessList($orderDetail);
        });

        static::updated(function ($orderDetail) {
            addStudentOrAccessList($orderDetail);
        });
    }
}
