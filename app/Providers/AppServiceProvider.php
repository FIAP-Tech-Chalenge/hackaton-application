<?php

namespace App\Providers;

use App\Infra\Adapters\Shared\HorariosDisponiveisCommand;
use App\Infra\Adapters\Shared\HorariosDisponiveisMapper;
use App\Infra\Adapters\Shared\MedicoMapper;
use App\Infra\Adapters\Shared\PacienteMapper;
use App\Infra\Adapters\Shared\ReservarHorarioCommand;
use App\Infra\Adapters\Shared\ReservarHorarioMapper;
use App\Modules\Shared\Gateways\Horarios\HorariosDisponiveisCommandInterface;
use App\Modules\Shared\Gateways\Horarios\HorariosDisponiveisMapperInterface;
use App\Modules\Shared\Gateways\Pacientes\PacienteMapperInterface;
use App\Modules\Shared\Gateways\Reservas\ReservarHorarioCommandInterface;
use App\Modules\Shared\Gateways\Reservas\ReservarHorarioMapperInterface;
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
        $this->app->bind(ReservarHorarioMapperInterface::class, ReservarHorarioMapper::class);

        $this->app->bind(PacienteMapperInterface::class, PacienteMapper::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
