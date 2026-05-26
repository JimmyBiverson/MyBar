<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('partials.stock-alert', function ($view) {
            $lowStockCount = Product::whereColumn('current_stock', '<=', 'reorder_level')->count();
            $lowStockItems = Product::whereColumn('current_stock', '<=', 'reorder_level')
                ->orderBy('current_stock')
                ->limit(5)
                ->get();

            $view->with(compact('lowStockCount', 'lowStockItems'));
        });
    }
}
