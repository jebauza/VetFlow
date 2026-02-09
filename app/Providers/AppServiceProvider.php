<?php

namespace App\Providers;

use App\Modules\User\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
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
        // Throws an exception if lazy loading occurs.
        Model::preventLazyLoading(!app()->isProduction());

        // 🔥 Superadmin bypass GLOBAL
        Gate::before(function (User $user, string $ability) {
            // Superadmin bypass total
            if ($user->{User::IS_SUPERADMIN}) {
                return true;
            }

            if ($user->hasPermissionTo('admin')) {
                return true;
            }

            return null;
        });

        // Access control via Gate
        // public static $permissions = [
        //     'reseller' => [self::RESELLER],
        //     'manager' => [self::MANAGER],
        //     'editor' => [self::EDITOR],
        //     'reseller|manager' => [self::RESELLER, self::MANAGER],
        // ];

        // foreach (Role::$permissions as $action => $roles) {
        //     Gate::define(
        //         $action,
        //         function (User $user) use ($roles) {
        //             return $user->hasAnyRoleName($roles)
        //                 ? Response::allow()
        //                 : Response::deny(__('You do not have the required role for this access.'));
        //         }
        //     );
        // }
    }
}
