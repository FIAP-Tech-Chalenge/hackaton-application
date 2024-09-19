<?php

namespace App\Jobs\Agendamento;

use App\Enums\StatusHorarioEnum;
use App\Modules\Pacientes\Entities\ReservaEntity;
use App\Modules\Shared\Gateways\ReservarHorarioCommandInterface;
use App\Modules\Shared\Gateways\ReservarHorarioMapperInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ValidacaoDeReservaJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ReservaEntity $reservaEntity,
        private readonly ReservarHorarioCommandInterface $reservarHorarioCommand,
        private readonly ReservarHorarioMapperInterface $reservarHorarioMapper,
    ) {
    }

    public function handle(): void
    {
        $horarioReservado = $this->reservarHorarioMapper->getReserva($this->reservaEntity->horarioDisponivelUuid);
        if (!$horarioReservado) {
            // enviar email para o paciente informando que a reserva não foi confirmada
        }

        $horarioReservado->setStatus(StatusHorarioEnum::CONFIRMADO);
        $this->reservarHorarioCommand->confirmarReserva($horarioReservado);
        // enviar email para o paciente informando que a reserva foi confirmada
        // enviar email para o médico informando que a reserva foi confirmada
    }
}
