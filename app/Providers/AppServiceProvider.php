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
            $products = Product::orderBy('current_stock')->get();

            $lowStockItems = $products->filter(fn ($p) => $p->stock_status === 'low')->take(5);
            $mediumStockItems = $products->filter(fn ($p) => $p->stock_status === 'medium')->take(5);
            $lowStockCount = $products->filter(fn ($p) => $p->stock_status === 'low')->count();
            $mediumStockCount = $products->filter(fn ($p) => $p->stock_status === 'medium')->count();

            $view->with(compact('lowStockCount', 'mediumStockCount', 'lowStockItems', 'mediumStockItems'));
        });
    }
}
