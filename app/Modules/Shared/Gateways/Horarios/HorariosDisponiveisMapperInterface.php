<?php

namespace App\Modules\Shared\Gateways\Horarios;

use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use App\Modules\Shared\Entities\HorarioEntity;
use Carbon\Carbon;
use Ramsey\Uuid\UuidInterface;

interface HorariosDisponiveisMapperInterface
{
    public function possuiAgendaNoDia(UuidInterface $medicoUuid, Carbon $data): bool;

    public function getConflitos(UuidInterface $medicoUuid, Carbon $data, IntervaloEntity $novo): IntervalosCollection;

    public function getHorarioDisponivelComLockForUpdate(UuidInterface $horarioUuid): ?HorarioEntity;
}
