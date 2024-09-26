<?php

namespace App\Infra\Database\Mappers\Horarios;

use App\Enums\StatusHorarioEnum;
use App\Helpers\BuilderHelper;
use App\Models\HorarioDisponivel;
use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use App\Modules\Shared\Entities\HorarioEntity;
use App\Modules\Shared\Gateways\Horarios\HorariosDisponiveisMapperInterface;
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
            ->whereDate('data', '=', $data)
            ->exists();
    }

    public function getConflitos(UuidInterface $medicoUuid, Carbon $data, IntervaloEntity $novo): IntervalosCollection
    {
        $conflitosExistentes = BuilderHelper::overlap(
            baseBuilder: HorarioDisponivel::query(),
            primeiraColuna: 'hora_inicio',
            segundaColuna: 'hora_fim',
            primeiroValor: $novo->inicioDoIntervalo,
            segundoValor: $novo->finalDoIntervalo
        )
            ->where('medico_uuid', '=', $medicoUuid->toString())
            ->whereDate('data', '=', $data)
            ->where('status', '=', StatusHorarioEnum::DISPONIVEL->value)
            ->toBase()
            ->get();

        $conflitos = new IntervalosCollection();
        foreach ($conflitosExistentes as $conflito) {
            $intervalo = new IntervaloEntity(
                inicioDoIntervalo: Carbon::parse($conflito->hora_inicio),
                finalDoIntervalo: Carbon::parse($conflito->hora_fim),
                statusHorarioEnum: StatusHorarioEnum::from($conflito->status)
            );

            $conflitos->addWithKey(intervalo: $intervalo, key: $conflito->uuid);
        }

        return $conflitos;
    }

    public function getHorarioDisponivelComLockForUpdate(UuidInterface $horarioUuid): ?HorarioEntity
    {
        $horario = HorarioDisponivel::query()
            ->select('medico_uuid', 'data', 'hora_inicio', 'hora_fim', 'status')
            ->where('uuid', '=', $horarioUuid->toString())
            ->lockForUpdate()
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
}
