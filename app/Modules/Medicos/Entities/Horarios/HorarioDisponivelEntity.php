<?php

namespace App\Modules\Medicos\Entities\Horarios;

use App\Enums\StatusHorarioEnum;
use Carbon\Carbon;
use Ramsey\Uuid\UuidInterface;

class HorarioDisponivelEntity
{
    public readonly UuidInterface $medicoUuid;
    public readonly Carbon $data;
    private Carbon $horaInicio;
    private Carbon $horaFim;
    private StatusHorarioEnum $status;

    private function __construct()
    {
    }

    public static function buildHorarioDisponivel(UuidInterface $medicoUuid, Carbon $data): self
    {
        $object = new self();
        $object->status = StatusHorarioEnum::DISPONIVEL;
        $object->medicoUuid = $medicoUuid;
        $object->data = $data;

        return $object;
    }

    public function getHoraInicio(): Carbon
    {
        return $this->horaInicio;
    }

    public function setHoraInicio(Carbon $horaInicio): self
    {
        $this->horaInicio = $horaInicio;
        return $this;
    }

    public function getHoraFim(): Carbon
    {
        return $this->horaFim;
    }

    public function setHoraFim(Carbon $horaFim): self
    {
        $this->horaFim = $horaFim;
        return $this;
    }

    public function getStatus(): StatusHorarioEnum
    {
        return $this->status;
    }
}
