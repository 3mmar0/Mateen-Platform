<?php

namespace App\Providers;

use App\Enums\Role;
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
        Gate::before(fn ($user) => $user->isRole(Role::Admin) ? true : null);
        Gate::define('manage-schedules', fn ($user) => $user->isRole(Role::Admin, Role::Supervisor));
        Gate::define('support-users', fn ($user) => $user->isRole(Role::Admin, Role::Support));
        Gate::define('view-stats', fn ($user) => $user->isRole(Role::Admin, Role::Supervisor, Role::Teacher));
    }
}
