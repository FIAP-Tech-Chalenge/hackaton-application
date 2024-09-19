<?php

namespace App\Infra\Adapters\Shared;

use App\Enums\StatusHorarioEnum;
use App\Models\PacienteHorarioDisponivel;
use App\Modules\Shared\Entities\HorarioReservadoEntity;
use App\Modules\Shared\Entities\MedicoEntity;
use App\Modules\Shared\Entities\PacienteEntity;
use App\Modules\Shared\Gateways\ReservarHorarioMapperInterface;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ReservarHorarioMapper implements ReservarHorarioMapperInterface
{

    public function getReserva(UuidInterface $horarioDisponivelUuid): ?HorarioReservadoEntity
    {
        $reserva = PacienteHorarioDisponivel::query()
            ->select('uuid', 'horario_disponivel_uuid', 'paciente_uuid')
            ->where('horario_disponivel_uuid', '=', $horarioDisponivelUuid->toString())
            ->with([
                'horarioDisponivel.medico',
                'horarioDisponivel.medico.user:uuid,email',
                'paciente.user:uuid,email'
            ])
            ->first();

        if (!$reserva) {
            return null;
        }

        return new HorarioReservadoEntity(
            horarioUuid: $horarioDisponivelUuid,
            medicoEntity: new MedicoEntity(
                uuid: Uuid::fromString($reserva->horarioDisponivel->medico_uuid),
                nome: $reserva->horarioDisponivel->medico->nome,
                crm: $reserva->horarioDisponivel->medico->crm,
                email: $reserva->horarioDisponivel->medico->user->email
            ),
            pacienteEntity: new PacienteEntity(
                uuid: Uuid::fromString($reserva->paciente_uuid),
                nome: $reserva->paciente->nome,
                cpf: $reserva->paciente->cpf,
                email: $reserva->paciente->user->email
            ),
            data: Carbon::parse($reserva->horarioDisponivel->data),
            horaInicio: Carbon::parse($reserva->horarioDisponivel->horaInicio),
            horaFim: Carbon::parse($reserva->horarioDisponivel->horaFim),
            status: StatusHorarioEnum::from($reserva->horarioDisponivel->status)
        );
    }
}
