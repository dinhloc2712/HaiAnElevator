<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Implicitly grant "Admin" role all permissions
        Gate::before(function ($user, $ability) {
            if ($user->role && in_array(strtolower($user->role->name), ['admin', 'super admin', 'administrator'])) {
                return true;
            }
        });

        // Define gates for all permissions
        try {
            // Check if Permission model exists and table has data to avoid errors during migration
            if (class_exists(\App\Models\Permission::class) && \Illuminate\Support\Facades\Schema::hasTable('permissions')) {
                $permissions = \App\Models\Permission::all();
                foreach ($permissions as $permission) {
                    Gate::define($permission->name, function ($user) use ($permission) {
                        return $user->hasPermission($permission->name);
                    });
                }
            }
        } catch (\Exception $e) {
            // Log or ignore exception during early bootstrap/migration
        }
    }
}
