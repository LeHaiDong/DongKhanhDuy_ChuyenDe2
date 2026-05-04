<?php

namespace App\Providers;

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
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Share cart count and favorites count with all views
        view()->composer('*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                
                // Get cart count
                if (session()->has('cart')) {
                    $cartCount = collect(session('cart'))->sum('quantity');
                } else {
                    $cartCount = \App\Models\Cart::forUser($user->id)->sum('quantity');
                }
                
                // Get favorites count
                $favoritesCount = $user->favorites()->count();
                
                $view->with('cartCount', $cartCount);
                $view->with('favoritesCount', $favoritesCount);
            } else {
                // For guests, only get session cart count
                $cartCount = session()->has('cart') ? collect(session('cart'))->sum('quantity') : 0;
                $view->with('cartCount', $cartCount);
                $view->with('favoritesCount', 0);
            }
        });
    }
}
