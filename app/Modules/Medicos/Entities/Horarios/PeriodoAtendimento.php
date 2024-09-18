<?php

namespace App\Modules\Medicos\Entities\Horarios;

use App\Enums\IntervalosDeAgendamentosEnum;
use App\Enums\StatusHorarioEnum;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use App\Modules\Shared\Exceptions\Horarios\HoraInicialMaiorQueFinalException;
use Carbon\Carbon;

class PeriodoAtendimento
{
    public function __construct(public Carbon $inicioAtendimento, public Carbon $fimAtendimento)
    {
        $this->validarHorario();
    }

    /**
     * @throws HoraInicialMaiorQueFinalException
     */
    public function validarHorario(): void
    {
        if ($this->inicioAtendimento->greaterThanOrEqualTo($this->fimAtendimento)) {
            throw new HoraInicialMaiorQueFinalException('Horário inválido');
        }
    }

    public function montarAgendaDoDia(
        IntervalosDeAgendamentosEnum $intervalosDeAgendamentosEnum,
        IntervalosCollection $intervalosIndisponiveis
    ): IntervalosCollection {
        $intervalos = new IntervalosCollection();
        $inicio = $this->inicioAtendimento->copy();
        $fim = $this->inicioAtendimento->copy()->addMinutes($intervalosDeAgendamentosEnum->value);

        while ($fim->lessThanOrEqualTo($this->fimAtendimento)) {
            //criar intervalo apenas se não estiver indisponível
            $podeDisponibilizar = $this->podeDisponibilizar($intervalosIndisponiveis, $inicio, $fim);
            if ($podeDisponibilizar) {
                $intervalos->add(
                    intervalo: new IntervaloEntity(
                        inicioDoIntervalo: $inicio,
                        finalDoIntervalo: $fim,
                        statusHorarioEnum: StatusHorarioEnum::DISPONIVEL
                    )
                );
            }
            $inicio = $inicio->copy()->addMinutes($intervalosDeAgendamentosEnum->value);
            $fim = $fim->copy()->addMinutes($intervalosDeAgendamentosEnum->value);
        }

        return $intervalos;
    }

    private function podeDisponibilizar(IntervalosCollection $indisponiveis, Carbon $inicio, Carbon $fim): bool
    {
        foreach ($indisponiveis->getIntervalos() as $intervalo) {
            if ($inicio->between($intervalo->inicioDoIntervalo, $intervalo->finalDoIntervalo)) {
                return false;
            }
            if ($fim->between($intervalo->inicioDoIntervalo, $intervalo->finalDoIntervalo)) {
                return false;
            }
        }
        return true;
    }
}
