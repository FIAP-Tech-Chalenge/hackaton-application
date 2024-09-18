<?php

namespace App\Modules\Shared\Gateways;

use Carbon\Carbon;
use Ramsey\Uuid\UuidInterface;

interface HorariosDisponiveisMapperInterface
{
    public function possuiAgendaNoDia(UuidInterface $medicoUuid, Carbon $data): bool;
}
