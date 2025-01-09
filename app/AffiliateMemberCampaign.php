<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class AffiliateMemberCampaign
 * @package App
 *
 * @mixin \Eloquent
 */
class AffiliateMemberCampaign extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    protected $appends = ['productOrCategoryNames', 'productOrCategoryIds'];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(
            AccountDomain::class,
            'account_domain_id'
        )->withoutGlobalScopes();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(UsersProduct::class);
    }

    public function conditionGroups(): HasMany
    {
        return $this->hasMany(AffiliateMemberConditionGroup::class)->orderBy('priority');
    }

    public function getProductOrCategoryNamesAttribute()
    {
        $productTitles = $this->products()->pluck('title');
        $categoryNames = $this->categories()->pluck('name');

        if ($productTitles->count() !== 0) {
            return $productTitles;
        }

        if ($categoryNames->count() !== 0) {
            return $categoryNames;
        }

        return collect(); // means All Products
    }

    public function hasProducts(): bool
    {
        return $this->products->count() !== 0;
    }

    public function hasCategories(): bool
    {
        return $this->categories->count() !== 0;
    }

    /**
     * Identical to getProductOrCategoryNamesAttribute, except
     * it's now return a collection of ids
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProductOrCategoryIdsAttribute(): Collection
    {
        $productIds = $this->products()->pluck('id');
        $categoryIds = $this->categories()->pluck('id');

        if ($productIds->count() !== 0) {
            return $productIds;
        }

        if ($categoryIds->count() !== 0) {
            return $categoryIds;
        }

        return collect(); // means All Products
    }

    public static function boot()
    {
        parent::boot();
        static::created(function ($campaign) {
            $campaign->calculateTotalCampaign($campaign);
        });

        static::deleted(function ($campaign) {
            $campaign->calculateTotalCampaign($campaign);
        });
    }

    protected function calculateTotalCampaign($campaign)
    {
        $account = Account::find($campaign->account_id);
        $accountPlan = $account->accountPlanTotal;
        $accountPlan->total_affiliate_campaign = $account->affiliateCampaign->count();
        $accountPlan->save();
    }
}
