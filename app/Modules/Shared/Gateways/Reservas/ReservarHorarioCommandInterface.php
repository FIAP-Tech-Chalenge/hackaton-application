<?php

namespace App\Modules\Shared\Gateways\Reservas;

use App\Modules\Pacientes\Entities\ReservaEntity;
use App\Modules\Shared\Entities\HorarioEntity;
use App\Modules\Shared\Entities\HorarioReservadoEntity;
use Ramsey\Uuid\UuidInterface;

interface ReservarHorarioCommandInterface
{
    public function reservarHorario(HorarioEntity $horarioEntity, UuidInterface $pacienteUuid): ?ReservaEntity;

    public function confirmarReserva(HorarioReservadoEntity $horarioEntity);

    public function cancelarReserva(HorarioReservadoEntity $horarioReservado): void;
}
