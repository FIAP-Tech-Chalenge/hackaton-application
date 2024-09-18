<?php

namespace App\Infra\Adapters\Shared;

use App\Enums\StatusHorarioEnum;
use App\Models\Medico;
use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use App\Modules\Shared\Gateways\HorariosDisponiveisCommandInterface;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class HorariosDisponiveisCommand implements HorariosDisponiveisCommandInterface
{
    public function criarAgendaDoDia(
        UuidInterface $medicoUuid,
        Carbon $data,
        IntervalosCollection $horariosDisponiveis
    ): void {
        $medico = Medico::query()
            ->select('uuid')
            ->where('uuid', '=', $medicoUuid->toString())
            ->firstOrFail();
        $horarios = [];

        /** @var IntervaloEntity $horario */
        foreach ($horariosDisponiveis as $horario) {
            $horarios[] = [
                'uuid' => Uuid::uuid7()->toString(),
                'medico_uuid' => $medicoUuid->toString(),
                'data' => $data,
                'hora_inicio' => $horario->inicioDoIntervalo,
                'hora_fim' => $horario->finalDoIntervalo,
                'status' => StatusHorarioEnum::DISPONIVEL->value,
            ];
        }
        $medico->horariosDisponiveis()->createMany($horarios);
    }
}
