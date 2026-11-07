<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Domain\ScientificData\Repositories\ScientificDataRepositoryInterface;
use App\Domain\ScientificData\Repositories\PostGisScientificDataRepository;
use App\Infrastructure\Python\Contracts\PythonComputationBridgeInterface;
use App\Infrastructure\Python\PythonComputationBridge;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ScientificDataRepositoryInterface::class, PostGisScientificDataRepository::class);
        $this->app->singleton(PythonComputationBridgeInterface::class, PythonComputationBridge::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register anonymous component path for layouts (<x-layouts.public>, <x-layouts.dashboard>)
        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
    }
}
