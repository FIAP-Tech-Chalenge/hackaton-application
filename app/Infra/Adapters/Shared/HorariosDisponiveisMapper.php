<?php

namespace App\Infra\Adapters\Shared;

use App\Enums\StatusHorarioEnum;
use App\Helpers\BuilderHelper;
use App\Models\HorarioDisponivel;
use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use App\Modules\Shared\Entities\HorarioEntity;
use App\Modules\Shared\Gateways\HorariosDisponiveisMapperInterface;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class HorariosDisponiveisMapper implements HorariosDisponiveisMapperInterface
{
    public function possuiAgendaNoDia(UuidInterface $medicoUuid, Carbon $data): bool
    {
        return HorarioDisponivel::query()
            ->where('medico_uuid', '=', $medicoUuid->toString())
            ->where('status', '=', StatusHorarioEnum::DISPONIVEL->value)
            ->where('data', '=', $data)
            ->exists();
    }

    public function getConflitos(UuidInterface $medicoUuid, Carbon $data, IntervaloEntity $novo): IntervalosCollection
    {
        $conflitosExistentes = BuilderHelper::overlap(
            baseBuilder: HorarioDisponivel::query(),
            primeiraColuna: 'hora_inicio',
            segundaColuna: 'hora_fim',
            primeiroValor: $novo->inicioDoIntervalo->format('H:i'),
            segundoValor: $novo->finalDoIntervalo->format('H:i')
        )
            ->where('medico_uuid', '=', $medicoUuid->toString())
            ->where('data', '=', $data)
            ->where('status', '=', StatusHorarioEnum::DISPONIVEL->value)
            ->toBase()
            ->get();

        $intervalosCollection = new IntervalosCollection();
        foreach ($conflitosExistentes as $conflito) {
            $intervalo = new IntervaloEntity(
                inicioDoIntervalo: Carbon::parse($conflito->hora_inicio),
                finalDoIntervalo: Carbon::parse($conflito->hora_fim),
                statusHorarioEnum: StatusHorarioEnum::from($conflito->status)
            );

            $intervalosCollection->addWithKey(intervalo: $intervalo, key: $conflito->uuid);
        }

        return $intervalosCollection;
    }

    public function getHorarioDisponivel(UuidInterface $horarioUuid): ?HorarioEntity
    {
        $horario = HorarioDisponivel::query()
            ->select('medico_uuid', 'data', 'hora_inicio', 'hora_fim', 'status')
            ->where('uuid', '=', $horarioUuid->toString())
            ->first();

        if (!$horario) {
            return null;
        }

        return new HorarioEntity(
            horarioUuid: $horarioUuid,
            medicoUuid: Uuid::fromString($horario->medico_uuid),
            data: Carbon::parse($horario->data),
            horaInicio: Carbon::parse($horario->hora_inicio),
            horaFim: Carbon::parse($horario->hora_fim),
            status: StatusHorarioEnum::from($horario->status)
        );
    }

    public function getPacienteDoHorarioAlocado(UuidInterface $horarioUuid): ?UuidInterface
    {
        // TODO: Implement getHorarioAlocado() method.
    }
}
