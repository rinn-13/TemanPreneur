<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
        // Register specific policies
        Gate::policy(\App\Models\Business::class, \App\Policies\BusinessPolicy::class);
        Gate::policy(\App\Models\Product::class, \App\Policies\ProductPolicy::class);
        Gate::policy(\App\Models\Order::class, \App\Policies\OrderPolicy::class);
        // Ensure BlogPolicy is available even if AuthServiceProvider is not loaded
        Gate::policy(\App\Models\Blog::class, \App\Policies\BlogPolicy::class);
    }
}
