<?php

namespace App\Providers;

use App\Infra\Adapters\Shared\HorariosDisponiveisCommand;
use App\Infra\Adapters\Shared\HorariosDisponiveisMapper;
use App\Modules\Shared\Gateways\HorariosDisponiveisCommandInterface;
use App\Modules\Shared\Gateways\HorariosDisponiveisMapperInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(HorariosDisponiveisCommandInterface::class, HorariosDisponiveisCommand::class);
        $this->app->bind(HorariosDisponiveisMapperInterface::class, HorariosDisponiveisMapper::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
