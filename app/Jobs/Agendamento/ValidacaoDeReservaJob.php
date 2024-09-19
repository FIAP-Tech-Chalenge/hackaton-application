<?php

namespace App\Jobs\Agendamento;

use App\Enums\StatusHorarioEnum;
use App\Models\User;
use App\Modules\Pacientes\Entities\ReservaEntity;
use App\Modules\Shared\Gateways\ReservarHorarioCommandInterface;
use App\Modules\Shared\Gateways\ReservarHorarioMapperInterface;
use App\Notifications\Agendamento\Reserva\ReservaConfirmadaMedicoMail;
use App\Notifications\Agendamento\Reserva\ReservaConfirmadaPacienteMail;
use App\Notifications\Agendamento\Reserva\ReservaReprovadaPacienteMail;
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
        $horarioReservado = $this->reservarHorarioMapper
            ->getDetalhesDaReserva($this->reservaEntity->horarioDisponivelUuid);
        $pacienteUser = User::query()
            ->select('uuid', 'email')
            ->whereExists(function ($query) {
                $query->select('uuid')
                    ->from('pacientes')
                    ->where('uuid', '=', $this->reservaEntity->pacienteUuid->toString())
                    ->whereColumn('user_uuid', 'users.uuid');
            })
            ->first();

        if ($horarioReservado->getStatus() !== StatusHorarioEnum::DISPONIVEL) {
            $pacienteUser->notify(new ReservaReprovadaPacienteMail($horarioReservado));
            return;
        }

        $medicoUser = User::query()
            ->select('uuid', 'email')
            ->whereExists(function ($query) use ($horarioReservado) {
                $query->select('uuid')
                    ->from('medicos')
                    ->where('uuid', '=', $horarioReservado->medicoEntity->uuid->toString())
                    ->whereColumn('user_uuid', 'users.uuid');
            })
            ->first();


        $horarioReservado->setStatus(StatusHorarioEnum::CONFIRMADO);
        $this->reservarHorarioCommand->confirmarReserva($horarioReservado);
        // enviar email para o paciente informando que a reserva foi confirmada
        $pacienteUser->notify(new ReservaConfirmadaPacienteMail($horarioReservado));
        // enviar email para o médico informando que a reserva foi confirmada
        $medicoUser->notify(new ReservaConfirmadaMedicoMail($horarioReservado));
    }
}
