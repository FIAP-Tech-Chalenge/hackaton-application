<?php

namespace App\Modules\Medicos\Entities\Horarios;

use App\Enums\StatusHorarioEnum;
use App\Modules\Shared\Exceptions\Horarios\HoraInicialMaiorQueFinalException;
use Carbon\Carbon;
use Ramsey\Uuid\UuidInterface;

class IntervaloEntity
{
    private UuidInterface $uuid;

    /**
     * @throws HoraInicialMaiorQueFinalException
     */
    public function __construct(
        public readonly Carbon $inicioDoIntervalo,
        public readonly Carbon $finalDoIntervalo,
        public readonly StatusHorarioEnum $statusHorarioEnum
    ) {
        $this->validarHorario();
    }

    /**
     * @throws HoraInicialMaiorQueFinalException
     */
    public function validarHorario(): void
    {
        if ($this->inicioDoIntervalo->greaterThanOrEqualTo($this->finalDoIntervalo)) {
            throw new HoraInicialMaiorQueFinalException('Horário inválido');
        }
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function setUuid(UuidInterface $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }
}
