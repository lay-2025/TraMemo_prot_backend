<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Travel\Repositories\TravelRepositoryInterface;
use App\Infrastructure\Travel\Repositories\TravelRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TravelRepositoryInterface::class, TravelRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
