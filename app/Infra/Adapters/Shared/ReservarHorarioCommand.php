<?php

namespace App\Infra\Adapters\Shared;

use App\Enums\StatusHorarioEnum;
use App\Models\HorarioDisponivel;
use App\Models\PacienteHorarioDisponivel;
use App\Modules\Pacientes\Entities\ReservaEntity;
use App\Modules\Shared\Entities\HorarioEntity;
use App\Modules\Shared\Entities\HorarioReservadoEntity;
use App\Modules\Shared\Exceptions\Horarios\HorarioNaoDisponivelException;
use App\Modules\Shared\Gateways\ReservarHorarioCommandInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Uid\Ulid;

class ReservarHorarioCommand implements ReservarHorarioCommandInterface
{
    public function reservarHorario(HorarioEntity $horarioEntity, UuidInterface $pacienteUuid): ReservaEntity
    {
        $horarioAtualizado = HorarioDisponivel::query()
            ->where('uuid', '=', $horarioEntity->horarioUuid->toString())
            ->where('status', '=', StatusHorarioEnum::DISPONIVEL->value)
            ->where('medico_uuid', '=', $horarioEntity->medicoUuid->toString())
            ->update([
                'status' => StatusHorarioEnum::RESERVADO->value
            ]);
        if (!$horarioAtualizado) {
            throw new HorarioNaoDisponivelException('Horário não disponível', 400);
        }
        $assinaturaConfirmacao = Ulid::generate();
        PacienteHorarioDisponivel::query()->create([
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
}