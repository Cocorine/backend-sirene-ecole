<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ServiceLayerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Services
        $this->app->bind(
            \App\Services\Contracts\PermissionServiceInterface::class,
            \App\Services\PermissionService::class
        );
        $this->app->bind(
            \App\Services\Contracts\RoleServiceInterface::class,
            \App\Services\RoleService::class
        );
        $this->app->bind(
            \App\Services\Contracts\UserServiceInterface::class,
            \App\Services\UserService::class
        );
        $this->app->bind(
            \App\Services\Contracts\AuthServiceInterface::class,
            \App\Services\AuthService::class
        );
        $this->app->bind(
            \App\Services\Contracts\EcoleServiceInterface::class,
            \App\Services\EcoleService::class
        );
        $this->app->bind(
            \App\Services\Contracts\SiteServiceInterface::class,
            \App\Services\SiteService::class
        );
        $this->app->bind(
            \App\Services\Contracts\SireneServiceInterface::class,
            \App\Services\SireneService::class
        );

        // Repositories
        $this->app->bind(
            \App\Repositories\Contracts\PermissionRepositoryInterface::class,
            \App\Repositories\PermissionRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\RoleRepositoryInterface::class,
            \App\Repositories\RoleRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\UserInfoRepositoryInterface::class,
            \App\Repositories\UserInfoRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\OtpCodeRepositoryInterface::class,
            \App\Repositories\OtpCodeRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\EcoleRepositoryInterface::class,
            \App\Repositories\EcoleRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\SiteRepositoryInterface::class,
            \App\Repositories\SiteRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\SireneRepositoryInterface::class,
            \App\Repositories\SireneRepository::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
