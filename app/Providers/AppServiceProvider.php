<?php

namespace App\Providers;

use App\Support\Branding;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Branding::class, fn () => Branding::load());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('is_root', function ($user) {
            return $user->is_root;
        });

        Gate::define('access_finished_tickets', function ($user) {
            return $user->is_root || $user->can_access_finished_tickets;
        });

        View::composer('*', fn ($view) => $view->with('brand', branding()));
    }
}
