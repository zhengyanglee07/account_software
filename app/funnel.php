<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Auth;
use App\Page;
use App\Traits\BelongsToAccount;

use App\ReferralCampaign;

class funnel extends Model
{
    use SoftDeletes, BelongsToAccount;

    protected $guarded = [];

    public function landingpages()
    {
        return $this->hasMany(Page::class);
    }

    public static function funnelLandingPages($funnel)
    {
        if (!$funnel) return null;
        return $funnel->landingpages()->select(
            'index',
            'path',
            'reference_key',
            'is_published',
        )->get();
    }

    public static function boot()
    {
        parent::boot();
        static::created(function ($funnel) {
            $totalFunnel = Funnel::where('account_id', $funnel->account_id)->count();
            $accountPlan = Auth::user()->currentAccount()->accountPlanTotal;
            $accountPlan->total_funnel = $totalFunnel;
            $accountPlan->save();
        });

        static::deleted(function ($funnel) {
            $totalFunnel = Funnel::where('account_id', $funnel->account_id)->count();
            $totalLandingPage = Page::where('account_id', $funnel->account_id)->where('deleted_at', null)->count();
            $accountPlan = Auth::user()->currentAccount()->accountPlanTotal;
            $accountPlan->total_funnel = $totalFunnel;
            $accountPlan->total_landingpage = $totalLandingPage;
            $accountPlan->save();

            $campaign = ReferralCampaign::where('funnel_id', $funnel->id)->first();
            if ($campaign) {
                $campaign->funnel_id = null;
                $campaign->save();
            }
        });
    }
}
