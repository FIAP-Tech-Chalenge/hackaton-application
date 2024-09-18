<?php

namespace App\Infra\Adapters\Shared;

use App\Enums\StatusHorarioEnum;
use App\Models\HorarioDisponivel;
use App\Models\Medico;
use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use App\Modules\Shared\Gateways\HorariosDisponiveisCommandInterface;
use Carbon\Carbon;
use Ramsey\Uuid\UuidInterface;

class HorariosDisponiveisCommand implements HorariosDisponiveisCommandInterface
{
    public function criarAgendaDoDia(
        UuidInterface $medicoUuid,
        Carbon $data,
        IntervalosCollection $horariosDisponiveis
    ): void {
        $medico = Medico::query()
            ->where('uuid', '=', $medicoUuid->toString())
            ->firstOrFail();

        $horarios = [];

        /** @var IntervaloEntity $horario */
        foreach ($horariosDisponiveis as $horario) {
            $horarios[] = [
                'uuid' => $horario->getUuid()->toString(),
                'medico_uuid' => $medicoUuid->toString(),
                'data' => $data,
                'hora_inicio' => $horario->inicioDoIntervalo,
                'hora_fim' => $horario->finalDoIntervalo,
                'status' => StatusHorarioEnum::DISPONIVEL->value,
            ];
        }

        $medico->horariosDisponiveis()->createManyQuietly($horarios);
    }

    public function cancelarHorariosDisponiveis(UuidInterface $medicoUuid, array $horariosParaCancelarUuids): void
    {
        HorarioDisponivel::query()
            ->where('medico_uuid', '=', $medicoUuid->toString())
            ->whereIn('uuid', $horariosParaCancelarUuids)
            ->update([
                'status' => StatusHorarioEnum::INDISPONIVEL->value,
            ]);
    }
}
