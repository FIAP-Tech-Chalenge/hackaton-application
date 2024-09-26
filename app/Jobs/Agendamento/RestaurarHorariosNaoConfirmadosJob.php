<?php

namespace App\Jobs\Agendamento;

use App\Enums\StatusHorarioEnum;
use App\Models\HorarioDisponivel;
use App\Models\Paciente;
use App\Models\PacienteAgendamento;
use App\Modules\Pacientes\Entities\ReservaEntity;
use App\Modules\Shared\Entities\HorarioEntity;
use App\Modules\Shared\Entities\HorarioReservadoEntity;
use App\Notifications\Agendamento\Reserva\ReservaIndisponivelMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class RestaurarHorariosNaoConfirmadosJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ReservaEntity $reservaEntity,
        private readonly HorarioReservadoEntity $horarioReservadoEntity
    ) {
    }

    public function handle(): void
    {
        DB::transaction(function () {
            HorarioDisponivel::query()
                ->where(
                    'uuid',
                    '=',
                    $this->reservaEntity->horarioDisponivelUuid->toString()
                )
                ->update([
                    'status' => StatusHorarioEnum::DISPONIVEL->value
                ]);
            PacienteAgendamento::query()
                ->where(
                    'horario_disponivel_uuid',
                    '=',
                    $this->reservaEntity->horarioDisponivelUuid->toString()
                )
                ->delete();

            $paciente = Paciente::query()
                ->select('uuid', 'user_id')
                ->where('uuid', '=', $this->reservaEntity->pacienteUuid->toString())
                ->with('user:id,email')
                ->first();

            $paciente->user->notify(
                new ReservaIndisponivelMail(
                    new HorarioEntity(
                        horarioUuid: $this->horarioReservadoEntity->horarioUuid,
                        medicoUuid: $this->horarioReservadoEntity->medicoEntity->uuid,
                        data: $this->horarioReservadoEntity->data,
                        horaInicio: $this->horarioReservadoEntity->horaInicio,
                        horaFim: $this->horarioReservadoEntity->horaFim,
                        status: $this->horarioReservadoEntity->getStatus()
                    ),
                    $this->horarioReservadoEntity->medicoEntity,
                    $this->horarioReservadoEntity->pacienteEntity
                )
            );
        });
    }
}
