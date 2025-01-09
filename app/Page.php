<?php

namespace App;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'fonts' => 'array',
        'design' => 'array',
    ];

    public function scopeOfPublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOfPath($query, $accountId, $path)
    {
        return $query->whereAccountId($accountId)->wherePath($path);
    }

    public static function boot()
    {
        parent::boot();

        static::created(function () {
            $account = Auth::user()->currentAccount();
            $accountPlan =  Auth::user()->currentAccount()->accountPlanTotal;
            $totalLandingAccount = Page::where('account_id', $account->id)
                ->where('is_landing', true)->count();
            $totalOnlineStorePage = Page::where('account_id', $account->id)
                ->where('is_landing', false)->count();
            $accountPlan->total_landingpage = $totalLandingAccount;
            $accountPlan->total_online_store_pages = $totalOnlineStorePage;
            $accountPlan->save();
        });

        static::deleted(function () {
            $account = Auth::user()->currentAccount();
            $accountPlan =  Auth::user()->currentAccount()->accountPlanTotal;
            $totalLandingAccount = Page::where('account_id', $account->id)
                ->where('is_landing', true)->count();
            $totalOnlineStorePage = Page::where('account_id', $account->id)
                ->where('is_landing', false)->count();
            $accountPlan->total_landingpage = $totalLandingAccount;
            $accountPlan->total_online_store_pages = $totalOnlineStorePage;
            $accountPlan->save();
        });
    }

    public function popups()
    {
        return $this->BelongsToMany(Popup::class);
    }
}
