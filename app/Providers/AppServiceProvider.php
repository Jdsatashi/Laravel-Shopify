<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Shopify\Repositories\IPriceRepo;
use Modules\Shopify\Repositories\IStoreRepo;
use Modules\Shopify\Repositories\PriceRepo;
use Modules\Shopify\Repositories\StoreRepo;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // notes
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
