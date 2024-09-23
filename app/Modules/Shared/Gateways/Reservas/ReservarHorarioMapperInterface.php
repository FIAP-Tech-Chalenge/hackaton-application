<?php

namespace App\Modules\Shared\Gateways\Reservas;

use App\Modules\Shared\Entities\HorarioReservadoEntity;
use Ramsey\Uuid\UuidInterface;

interface ReservarHorarioMapperInterface
{

    public function getDetalhesDaReserva(UuidInterface $horarioDisponivelUuid): ?HorarioReservadoEntity;
}
