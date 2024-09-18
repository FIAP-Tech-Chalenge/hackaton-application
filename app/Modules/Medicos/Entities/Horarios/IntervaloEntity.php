<?php

namespace App\Modules\Medicos\Entities\Horarios;

use App\Enums\StatusHorarioEnum;
use App\Modules\Shared\Exceptions\Horarios\HoraInicialMaiorQueFinalException;
use Carbon\Carbon;

readonly class IntervaloEntity
{
    /**
     * @throws HoraInicialMaiorQueFinalException
     */
    public function __construct(
        public Carbon $inicioDoIntervalo,
        public Carbon $finalDoIntervalo,
        public StatusHorarioEnum $statusHorarioEnum
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
}
