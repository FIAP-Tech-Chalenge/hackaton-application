<?php

namespace App\Jobs\Agendamento;

use App\Enums\StatusHorarioEnum;
use App\Models\User;
use App\Modules\Pacientes\Entities\ReservaEntity;
use App\Modules\Shared\Entities\HorarioReservadoEntity;
use App\Modules\Shared\Gateways\Reservas\ReservarHorarioCommandInterface;
use App\Modules\Shared\Gateways\Reservas\ReservarHorarioMapperInterface;
use App\Notifications\Agendamento\Reserva\ReservaConfirmadaMedicoMail;
use App\Notifications\Agendamento\Reserva\ReservaConfirmadaPacienteMail;
use App\Notifications\Agendamento\Reserva\ReservaReprovadaPacienteMail;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

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
        $horarioReservado = $this->reservarHorarioMapper->getDetalhesDaReserva(
            $this->reservaEntity->horarioDisponivelUuid
        );
        if ($horarioReservado === null) {
            return;
        }
        try {
            DB::beginTransaction();
            $this->iniciarConfirmacaoDaReserva($horarioReservado);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            RestaurarHorariosNaoConfirmadosJob::dispatch(
                $this->reservaEntity,
                $horarioReservado
            );
        }
    }

    private function iniciarConfirmacaoDaReserva(HorarioReservadoEntity $horarioReservado): void
    {
        $pacienteUser = User::query()
            ->select('uuid', 'email', 'id')
            ->whereExists(function ($query) {
                $query->select('uuid')
                    ->from('pacientes')
                    ->where('uuid', '=', $this->reservaEntity->pacienteUuid->toString())
                    ->whereColumn('user_id', 'users.id');
            })
            ->first();
        // verificar se o horário ainda está disponível
        if ($horarioReservado->getStatus() !== StatusHorarioEnum::RESERVADO) {
            $this->reservarHorarioCommand->cancelarReserva($horarioReservado);

            $pacienteUser->notify(new ReservaReprovadaPacienteMail($horarioReservado));
            return;
        }

        /**
         * Verifica se o paciente que está confirmando a reserva é o mesmo que fez a reserva.
         * Este é o terceiro mecanismo de segurança para evitar problemas de concorrência:
         * 1. Verificação se já existe uma reserva para o horário [sync].
         * 2. Lock no banco de dados para a primeira requisição que chegar [sync].
         *    - app/Modules/Shared/Gateways/HorariosDisponiveisMapperInterface.php
         *    - getHorarioDisponivelComLockForUpdate(UuidInterface $uuid): ?HorarioDisponivelEntity;
         * 3. Verificação se o paciente que está confirmando a reserva é o mesmo que fez a reserva [async/fila].
         */
        if ($this->reservaEntity->pacienteUuid->toString() !== $horarioReservado->pacienteEntity->uuid->toString()) {
            $pacienteUser->notify(new ReservaReprovadaPacienteMail($horarioReservado));
            return;
        }

        $medicoUser = User::query()
            ->select('uuid', 'email', 'id')
            ->whereExists(function ($query) use ($horarioReservado) {
                $query->select('uuid')
                    ->from('medicos')
                    ->where('uuid', '=', $horarioReservado->medicoEntity->uuid->toString())
                    ->whereColumn('user_id', 'users.id');
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
