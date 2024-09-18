<?php

namespace App\Infra\Adapters\Shared;

use App\Models\HorarioDisponivel;
use App\Modules\Shared\Gateways\HorariosDisponiveisMapperInterface;
use Carbon\Carbon;
use Ramsey\Uuid\UuidInterface;

class HorariosDisponiveisMapper implements HorariosDisponiveisMapperInterface
{
    public function possuiAgendaNoDia(UuidInterface $medicoUuid, Carbon $data): bool
    {
        return HorarioDisponivel::query()
            ->where('medico_uuid', '=', $medicoUuid->toString())
            ->where('data', '=', $data)
            ->exists();
    }
}
