<?php

namespace App\Providers;

use App\Repositories\ImageRepository;
use App\Repositories\Interfaces\ImageInterface;
use App\Repositories\Interfaces\AccountInterface;
use App\Repositories\Interfaces\UserEmailTemplateInterface;
use App\Repositories\AccountRepository;
use App\Repositories\UserEmailTemplateRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services. Binding Repository interfaces to their concrete implementation.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserEmailTemplateInterface::class, UserEmailTemplateRepository::class);
        $this->app->bind(ImageInterface::class, ImageRepository::class);
        $this->app->bind(AccountInterface::class, AccountRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
