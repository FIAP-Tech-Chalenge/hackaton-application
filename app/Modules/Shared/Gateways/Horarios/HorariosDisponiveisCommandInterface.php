<?php

namespace App\Modules\Shared\Gateways\Horarios;

use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use Carbon\Carbon;
use Ramsey\Uuid\UuidInterface;

interface HorariosDisponiveisCommandInterface
{
    public function criarAgendaDoDia(
        UuidInterface $medicoUuid,
        Carbon $data,
        IntervalosCollection $horariosDisponiveis
    ): void;

    public function cancelarHorariosDisponiveis(UuidInterface $medicoUuid, array $horariosParaCancelarUuids): void;
}
