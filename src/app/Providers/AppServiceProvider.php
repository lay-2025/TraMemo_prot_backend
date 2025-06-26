<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Travel\Repositories\TravelRepositoryInterface;
use App\Infrastructure\Travel\Repositories\TravelRepository;
use App\Domain\Photo\Repositories\PhotoRepositoryInterface;
use App\Infrastructure\Photo\Repositories\PhotoRepository;
use App\Domain\Tag\Repositories\TagRepositoryInterface;
use App\Infrastructure\Tag\Repositories\TagRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TravelRepositoryInterface::class, TravelRepository::class);
        $this->app->bind(PhotoRepositoryInterface::class, PhotoRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
