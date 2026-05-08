<?php

namespace App\Providers;

use App\Models\Store;
use App\Models\StockItem;
use App\Observers\StoreObserver;
use App\Observers\StockItemObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observadores existentes
        StockItem::observe(StockItemObserver::class);

        // Novo observador para automação do CyberPanel
        Store::observe(StoreObserver::class);
    }
}