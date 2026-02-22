<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('role-admin', fn (User $user) => $user->hasRole(User::ROLE_ADMIN));
        Gate::define('role-operador', fn (User $user) => $user->hasRole(User::ROLE_OPERATOR));
        Gate::define('role-financeiro', fn (User $user) => $user->hasRole(User::ROLE_FINANCIAL));
        Gate::define('role-admin-operador', fn (User $user) => $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_OPERATOR]));
        Gate::define('role-admin-financeiro', fn (User $user) => $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_FINANCIAL]));
    }
}
