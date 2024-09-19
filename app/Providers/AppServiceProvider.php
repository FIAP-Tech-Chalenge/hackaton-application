<?php

namespace App\Providers;

use App\Infra\Adapters\Shared\HorariosDisponiveisCommand;
use App\Infra\Adapters\Shared\HorariosDisponiveisMapper;
use App\Infra\Adapters\Shared\PacienteMapper;
use App\Infra\Adapters\Shared\ReservarHorarioCommand;
use App\Modules\Shared\Gateways\HorariosDisponiveisCommandInterface;
use App\Modules\Shared\Gateways\HorariosDisponiveisMapperInterface;
use App\Modules\Shared\Gateways\PacienteMapperInterface;
use App\Modules\Shared\Gateways\ReservarHorarioCommandInterface;
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

        $this->app->bind(ReservarHorarioCommandInterface::class, ReservarHorarioCommand::class);
        $this->app->bind(PacienteMapperInterface::class, PacienteMapper::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
