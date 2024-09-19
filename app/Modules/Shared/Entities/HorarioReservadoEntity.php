<?php

namespace App\Modules\Shared\Entities;

use App\Enums\StatusHorarioEnum;
use Carbon\Carbon;
use Ramsey\Uuid\UuidInterface;

class HorarioReservadoEntity
{
    public function __construct(
        public readonly UuidInterface $horarioUuid,
        public readonly MedicoEntity $medicoEntity,
        public readonly PacienteEntity $pacienteEntity,
        public readonly Carbon $data,
        public readonly Carbon $horaInicio,
        public readonly Carbon $horaFim,
        private StatusHorarioEnum $status,
        public readonly string $assinaturaDoAgendamento
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
