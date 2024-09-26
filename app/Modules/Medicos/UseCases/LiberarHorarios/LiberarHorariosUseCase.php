<?php

namespace App\Modules\Medicos\UseCases\LiberarHorarios;

use App\Enums\IntervalosDeAgendamentosEnum;
use App\Modules\Medicos\Entities\Horarios\AgendaEntity;
use App\Modules\Medicos\Entities\Horarios\PeriodoAtendimento;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use App\Modules\Shared\Exceptions\Horarios\MedicoJaPossuiAgendaNoDiaException;
use App\Modules\Shared\Gateways\Horarios\HorariosDisponiveisCommandInterface;
use App\Modules\Shared\Gateways\Horarios\HorariosDisponiveisMapperInterface;
use Carbon\Carbon;
use Ramsey\Uuid\UuidInterface;

readonly class LiberarHorariosUseCase
{
    public function __construct(
        private HorariosDisponiveisCommandInterface $horariosDisponiveisCommand,
        private HorariosDisponiveisMapperInterface $horariosDisponiveisMapper
    ) {
    }

    /**
     * @throws MedicoJaPossuiAgendaNoDiaException
     */
    public function execute(
        PeriodoAtendimento $periodoAtendimento,
        IntervalosDeAgendamentosEnum $intervalo,
        IntervalosCollection $intervalosIndisponiveis,
        UuidInterface $medicoUuid,
        Carbon $data
    ): AgendaEntity {
        //verificar se o médico já tem agenda para o dia
        $possuiAgendaNoDia = $this->horariosDisponiveisMapper->possuiAgendaNoDia($medicoUuid, $data);
        if ($possuiAgendaNoDia) {
            throw new MedicoJaPossuiAgendaNoDiaException('Já existe uma agenda para o dia', 422);
        }

        // montagem da agenda do dia com os intervalos disponíveis
        $agendamentosNoDia = $periodoAtendimento->montarAgendaDoDia($intervalo, $intervalosIndisponiveis);
        $this->horariosDisponiveisCommand->criarAgendaDoDia($medicoUuid, $data, $agendamentosNoDia);

        return AgendaEntity::makeAgenda($agendamentosNoDia, $medicoUuid);
    }
}
