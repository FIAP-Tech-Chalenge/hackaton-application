<?php

namespace App\Modules\Shared\Entities;

use App\Enums\StatusHorarioEnum;
use Carbon\Carbon;
use Ramsey\Uuid\UuidInterface;

class HorarioEntity
{
    public function __construct(
        public readonly UuidInterface $horarioUuid,
        public readonly UuidInterface $medicoUuid,
        public readonly Carbon $data,
        public readonly Carbon $horaInicio,
        public readonly Carbon $horaFim,
        private StatusHorarioEnum $status,
    ) {
    }

    public function getStatus(): StatusHorarioEnum
    {
        return $this->status;
    }

    public function setStatus(StatusHorarioEnum $status): void
    {
        $this->status = $status;
    }
}
