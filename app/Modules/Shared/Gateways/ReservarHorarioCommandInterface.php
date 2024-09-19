<?php

namespace App\Modules\Shared\Gateways;

use App\Modules\Pacientes\Entities\ReservaEntity;
use App\Modules\Shared\Entities\HorarioEntity;
use Ramsey\Uuid\UuidInterface;

interface ReservarHorarioCommandInterface
{
    public function reservarHorario(HorarioEntity $horarioEntity, UuidInterface $pacienteUuid): ReservaEntity;
}
