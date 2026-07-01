<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\CompanyRepositoryInterface::class,
            \App\Repositories\Eloquent\CompanyRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\EnterpriseRepositoryInterface::class,
            \App\Repositories\Eloquent\EnterpriseRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\BranchRepositoryInterface::class,
            \App\Repositories\Eloquent\BranchRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
