<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
            $this->loadHelpers();
        }
    }

    protected function loadHelpers(){
        foreach(glob(__DIR__.'/../Helpers*.php')as $filename){
            require_once $filename;
        }
    }

    /**
     * Bootstrap any application services.
     *pr
     * @return void
     */
    public function boot()
    {
        if(app()->environment(['production']) || app()->environment(['staging'])){
            URL::forceScheme('https');
        }
        Schema::defaultStringLength(191);

        Relation::morphMap([
            'product' => 'App\UsersProduct',
            'form' => 'App\LandingPageForm',
            'funnel' => 'App\funnel',
            'order-discount' => 'App\Models\Promotion\PromotionOrderDiscount',
            'free-shipping' => 'App\Models\Promotion\PromotionFreeShipping',
            'product-discount' => 'App\Models\Promotion\PromotionProductDiscount',
        ]);
        Paginator::useBootstrapFive();
    }
}
