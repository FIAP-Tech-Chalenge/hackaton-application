<?php

namespace App\Jobs\Agendamento;

use App\Enums\StatusHorarioEnum;
use App\Models\User;
use App\Modules\Pacientes\Entities\ReservaEntity;
use App\Modules\Shared\Gateways\Reservas\ReservarHorarioCommandInterface;
use App\Modules\Shared\Gateways\Reservas\ReservarHorarioMapperInterface;
use App\Notifications\Agendamento\Reserva\ReservaConfirmadaMedicoMail;
use App\Notifications\Agendamento\Reserva\ReservaConfirmadaPacienteMail;
use App\Notifications\Agendamento\Reserva\ReservaReprovadaPacienteMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ValidacaoDeReservaJob implements ShouldQueue
{
    use Queueable;
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

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
        // verificar se o horário ainda está disponível
        if ($horarioReservado->getStatus() !== StatusHorarioEnum::RESERVADO) {
            $this->reservarHorarioCommand->cancelarReserva($horarioReservado);

            $pacienteUser->notify(new ReservaReprovadaPacienteMail($horarioReservado));
            return;
        }

        /**
         * Verificar se o paciente que está tentando confirmar a reserva é o mesmo que fez a reserva
         * garante que o paciente não está tentando confirmar a reserva de outra pessoa [problema de concorrência]
         * É o terceiro mecanismo de segurança para evitar problemas de concorrência
         * - O primeiro é a verificação se já existe uma reserva para o horário [sync]
         * - O segundo é um lock no banco de dados, onde a primeira requisição que chegar vai travar o registro e as demais vão esperar [sync]
         * - - app/Modules/Shared/Gateways/HorariosDisponiveisMapperInterface.php
         * - - getHorarioDisponivelComLockForUpdate(UuidInterface $uuid): ?HorarioDisponivelEntity;
         * - O terceiro é a verificação se o paciente que está tentando confirmar a reserva é o mesmo que fez a reserva [async/fila]
         **/
        if ($this->reservaEntity->pacienteUuid->toString() !== $horarioReservado->pacienteEntity->uuid->toString()) {
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
