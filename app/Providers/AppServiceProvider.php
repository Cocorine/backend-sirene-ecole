<?php

namespace App\Providers;

use App\Services\Contracts\NotificationServiceInterface;
use App\Services\NotificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
                $this->app->bind(NotificationServiceInterface::class, function ($app) {
            return new NotificationService(
                $app->make(Notification::class),
                $app->make(TechnicienRepositoryInterface::class),
                $app->make(EcoleRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
