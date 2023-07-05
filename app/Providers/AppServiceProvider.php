<?php

namespace App\Providers;

use App\Http\Repository\IPriceRepo;
use App\Http\Repository\IStoreRepo;
use App\Http\Repository\PriceRepo;
use App\Http\Repository\StoreRepo;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(IStoreRepo::class, StoreRepo::class);
        $this->app->bind(IPriceRepo::class, PriceRepo::class);
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
