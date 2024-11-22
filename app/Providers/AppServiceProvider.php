<?php

namespace App\Providers;

use App\Enums\Role;
use App\Services\AwsS3Service;
use App\Services\CloudStorageManager;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->isLocal()) {
            /**
             * Load IDE helper for non-production environment
             *
             * @see https://github.com/barryvdh/laravel-ide-helper
             */
            $this->app->register(IdeHelperServiceProvider::class);

            /**
             * Register Telescope service provider for non-production environment
             *
             * @see https://laravel.com/docs/11.x/telescope
             */
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->singleton(CloudStorageManager::class, function () {
            return new AwsS3Service();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Implicitly grant "super_user" role all permissions.
         * This works in the app by using gate-related functions like auth()->user->can() and @can()
         *
         * @see https://spatie.be/docs/laravel-permission/v6/basic-usage/super-admin
         */
        Gate::after(function ($user) {
            return $user->hasRole(Role::SUPER_USER) ? true : null;
        });

        /** Rate Limits */
        RateLimiter::for('login', function (Request $request) {
            $route = $request->route()->getName() ?? $request->route()->uri();
            $key = !$request->input('email')
                ? $request->input('username') . '-' . $request->ip() . '-' . $route
                : $request->input('email') . '-' . $request->ip() . '-' . $route;

            // If the user did not input a username or email when logging in
            if (!$key) {
                $key = $request->session()->getId() . '-' . $route;
            }

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('register', function (Request $request) {
            $route = $request->route()->getName() ?? $request->route()->uri();
            $key = $request->session()->getId() . '-' . $route;

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('send-email-notification', function (Request $request) {
            $userId = $request->user()?->id;
            $route = $request->route()->getName() ?? $request->route()->uri();
            $key = $userId
                ? $userId . '-' . $request->session()->getId() . '-' . $route
                : $request->session()->getId() . '-' . $route;

            return Limit::perMinute(1)->by($key);
        });
    }
}
