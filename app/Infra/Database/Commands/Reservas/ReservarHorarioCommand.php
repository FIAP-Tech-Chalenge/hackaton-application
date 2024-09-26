<?php

namespace App\Infra\Database\Commands\Reservas;

use App\Enums\StatusHorarioEnum;
use App\Models\HorarioDisponivel;
use App\Models\PacienteAgendamento;
use App\Modules\Pacientes\Entities\ReservaEntity;
use App\Modules\Shared\Entities\HorarioEntity;
use App\Modules\Shared\Entities\HorarioReservadoEntity;
use App\Modules\Shared\Gateways\Reservas\ReservarHorarioCommandInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Uid\Ulid;

class ReservarHorarioCommand implements ReservarHorarioCommandInterface
{
    public function reservarHorario(HorarioEntity $horarioEntity, UuidInterface $pacienteUuid): ?ReservaEntity
    {
        $horarioAtualizado = HorarioDisponivel::query()
            ->where('uuid', '=', $horarioEntity->horarioUuid->toString())
            ->where('status', '=', StatusHorarioEnum::DISPONIVEL->value)
            ->where('medico_uuid', '=', $horarioEntity->medicoUuid->toString())
            ->update([
                'status' => StatusHorarioEnum::RESERVADO->value
            ]);
        if ($horarioAtualizado === 0) {
            return null;
        }
        $assinaturaConfirmacao = Ulid::generate();
        PacienteAgendamento::query()->create([
            'horario_disponivel_uuid' => $horarioEntity->horarioUuid->toString(),
            'paciente_uuid' => $pacienteUuid->toString(),
            'assinatura_confirmacao' => $assinaturaConfirmacao,
        ]);

        return new ReservaEntity(
            horarioDisponivelUuid: $horarioEntity->horarioUuid,
            pacienteUuid: $pacienteUuid,
            assinaturaConfirmacao: $assinaturaConfirmacao,
            medicoUuid: $horarioEntity->medicoUuid,
        );
    }

    public function confirmarReserva(HorarioReservadoEntity $horarioEntity): void
    {
        HorarioDisponivel::query()
            ->where('uuid', '=', $horarioEntity->horarioUuid->toString())
            ->where('medico_uuid', '=', $horarioEntity->medicoEntity->uuid->toString())
            ->update([
                'status' => $horarioEntity->getStatus()->value
            ]);
    }

    public function cancelarReserva(HorarioReservadoEntity $horarioReservado): void
    {
        HorarioDisponivel::query()
            ->where('uuid', '=', $horarioReservado->horarioUuid->toString())
            ->where('medico_uuid', '=', $horarioReservado->medicoEntity->uuid->toString())
            ->update([
                'status' => StatusHorarioEnum::DISPONIVEL->value
            ]);
        PacienteAgendamento::query()
            ->where('horario_disponivel_uuid', '=', $horarioReservado->horarioUuid->toString())
            ->delete();
    }
}
