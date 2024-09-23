<?php

namespace App\Infra\Services\Pacientes;

use App\Enums\StatusHorarioEnum;
use App\Models\HorarioDisponivel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Ramsey\Uuid\UuidInterface;

class ListaHorariosDisponiveisService
{
    public function execute(UuidInterface $medicoUuid, Carbon $data): Collection
    {
        return HorarioDisponivel::query()
            ->where('medico_uuid', $medicoUuid->toString())
            ->where('data', $data)
            ->whereIn('status', [
                StatusHorarioEnum::DISPONIVEL->value,
                StatusHorarioEnum::RESERVADO->value,
                StatusHorarioEnum::CONFIRMADO->value,
            ])
            ->get()
            ->transform(fn(HorarioDisponivel $horarioDisponivel) => [
                'horario_uuid' => $horarioDisponivel->uuid,
                'hora_inicio' => $horarioDisponivel->hora_inicio,
                'hora_fim' => $horarioDisponivel->hora_fim,
                'status' => [
                    'label' => StatusHorarioEnum::from($horarioDisponivel->status)->name,
                    'value' => $horarioDisponivel->status,
                ],
            ]);
    }
}
