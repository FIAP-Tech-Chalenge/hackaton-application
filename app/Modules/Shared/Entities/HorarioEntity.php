<?php

namespace App\Modules\Shared\Entities;

use App\Enums\StatusHorarioEnum;
use Carbon\Carbon;
use Ramsey\Uuid\UuidInterface;

readonly class HorarioEntity
{
    public function __construct(
        public UuidInterface $horarioUuid,
        public UuidInterface $medicoUuid,
        public Carbon $data,
        public Carbon $horaInicio,
        public Carbon $horaFim,
        public StatusHorarioEnum $status,
    ) {
    }
}
