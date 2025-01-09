<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductModule;
use App\Services\ProductCourseService;
use Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UsersProduct
 *
 * @mixin \Eloquent
 */
class UsersProduct extends Model
{
    use softDeletes;
    protected $guarded = [];
    protected $table = 'products';

    protected $casts = [
        'image_collection' => 'array',
        'productImageCollection' => 'array'
    ];
    protected $appends = [
        'productTitle',
        'productDescription',
        'productImagePath',
        'productImageCollection',
        'productPrice',
        'productComparePrice',
        'isTaxable',
        'hasVariant',
        'SKU',
        'isEnrolled',
        'isPurchased',
        'salesChannel'
    ];

    public function modules()
    {
        return $this->hasMany(ProductModule::class, 'product_id');
    }

    public function product_variants()
    {
        return $this->hasMany('App\ProductVariant', 'product_id');
    }

    public function variant_values()
    {

        // Will write down the guideline in future if i get to understand how this works....
        return $this->hasManyThrough(
            'App\VariantValue',
            'App\ProductVariant',
            'product_id',
            'variant_id',
            'id',
            'variant_id'
        );
    }

    public function variants()
    {
        // return $this->hasMany('App\Variant', 'prodsuct_id');
        return $this->belongsToMany('App\Variant', 'product_variants', 'product_id', 'variant_id');
    }

    public function variant_details()
    {
        return $this->hasMany('App\VariantDetails', 'product_id');
    }

    public function product_option()
    {
        return $this->belongsToMany('App\productOption')->orderBy('sort_order', 'ASC');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Category', 'product_category', 'product_id', 'category_id')->withPivot('id')->orderByDesc('id');
    }

    public function subscription()
    {
        return $this->hasMany(ProductSubscription::class, 'users_products_id');
    }

    public function saleChannels()
    {
        return $this->belongsToMany(SaleChannel::class, 'product_sale_channels', 'users_product_id', 'sale_channel_id');
    }

    /**
     * Get all of the comments for the UsersProduct
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'users_product_id', 'id')->with('order');
    }

    public function badges()
    {
        return $this->belongsToMany('App\Badges')->withoutGlobalScopes();
    }


    public function getHasVariantAttribute()
    {
        return $this->variant_details()->exists();
    }

    public function getProductTitleAttribute()
    {
        return $this->attributes['title'] ?? '';
    }

    public function getProductDescriptionAttribute()
    {
        return $this->attributes['description'] ?? '';
    }

    public function getProductImagePathAttribute()
    {
        return $this->attributes['image_url'] ?? '';
    }

    public function getProductImageCollectionAttribute()
    {
        return json_decode($this->attributes['image_collection'] ?? '[]', true);
    }

    public function getProductPriceAttribute()
    {
        return $this->attributes['price'] ?? 0;
    }

    public function getProductComparePriceAttribute()
    {
        return $this->attributes['compare_price'] ?? null;
    }

    public function getIsTaxableAttribute()
    {
        return $this->attributes['is_taxable'] ?? 0;
    }

    public function getSKUAttribute()
    {
        return $this->attributes['sku'] ?? '';
    }

    public function getIsPurchasedAttribute()
    {
        return !!(new ProductCourseService($this))->checkIsPurchased();
    }
    public function getIsEnrolledAttribute()
    {
        return (new ProductCourseService($this))->checkIsEnrolled();
    }
    public function getSalesChannelAttribute()
    {
        return $this->saleChannels()->pluck('type');
    }

    public static function boot()
    {
        parent::boot();

        if (app()->environment('testing')) {
            return;
        }

        static::created(function ($usersProduct) {
            $account = Auth::user()->currentAccount();
            $totalProduct = UsersProduct::where('account_id', $account->id)->count();
            $accountPlan =  Auth::user()->currentAccount()->accountPlanTotal;
            $accountPlan->total_product = $totalProduct;
            $accountPlan->save();
        });

        static::deleted(function ($usersProcduct) {
            $account = Auth::user()->currentAccount();
            $totalProduct = UsersProduct::where('account_id', $account->id)->count();
            $accountPlan =  Auth::user()->currentAccount()->accountPlanTotal;
            $accountPlan->total_product = $totalProduct;
            $accountPlan->save();
        });
    }
}
