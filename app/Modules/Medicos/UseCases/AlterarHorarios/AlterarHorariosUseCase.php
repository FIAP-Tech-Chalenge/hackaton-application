<?php

namespace App\Modules\Medicos\UseCases\AlterarHorarios;

use App\Modules\Medicos\Entities\Horarios\AgendaEntity;
use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use App\Modules\Shared\Exceptions\Horarios\ExistemConflitosDeHorariosException;
use App\Modules\Shared\Gateways\HorariosDisponiveisCommandInterface;
use App\Modules\Shared\Gateways\HorariosDisponiveisMapperInterface;
use Carbon\Carbon;
use Ramsey\Uuid\UuidInterface;

readonly class AlterarHorariosUseCase
{
    public function __construct(
        private HorariosDisponiveisCommandInterface $horariosDisponiveisCommand,
        private HorariosDisponiveisMapperInterface $horariosDisponiveisMapper
    ) {
    }

    /**
     * @throws ExistemConflitosDeHorariosException
     */
    public function execute(
        array $horariosParaCancelarUuids,
        IntervaloEntity $novoIntervalo,
        UuidInterface $medicoUuid,
        Carbon $data
    ): AgendaEntity {
        if ($horariosParaCancelarUuids) {
            $conflitosDeHorarios = $this->horariosDisponiveisMapper->getConflitos($medicoUuid, $data, $novoIntervalo);
            if ($conflitosDeHorarios->count() > 0) {
                $this->verificaConflitos($conflitosDeHorarios, $horariosParaCancelarUuids);
                $this->horariosDisponiveisCommand->cancelarHorariosDisponiveis($medicoUuid, $horariosParaCancelarUuids);
            }
        }

        $intervalos = new IntervalosCollection();
        $intervalos->add($novoIntervalo);
        $this->horariosDisponiveisCommand->criarAgendaDoDia($medicoUuid, $data, $intervalos);

        return AgendaEntity::makeAgenda($intervalos, $medicoUuid);
    }

    /**
     * @throws ExistemConflitosDeHorariosException
     */
    public function verificaConflitos(IntervalosCollection $conflitosDeHorarios, array $horariosParaCancelarUuids): void
    {
        $horariosConflitantesUuid = array_keys($conflitosDeHorarios->toArray());
        $intersect = array_intersect($horariosConflitantesUuid, $horariosParaCancelarUuids);
        if (count($intersect) !== $conflitosDeHorarios->count()) {
            $horarioParaCancelar = array_values(array_diff($horariosConflitantesUuid, $horariosParaCancelarUuids));
            throw new ExistemConflitosDeHorariosException(
                message: 'Existem conflitos de horários. Para cancelar o agendamento, cancele os horários conflitantes.',
                code: 422,
                errors: [
                    'conflitos' => [
                        'itens' => $conflitosDeHorarios->toArray(),
                        'agendamento_para_cancelar' => $horariosParaCancelarUuids,
                        'horario_para_cancelar' => $horarioParaCancelar
                    ]
                ]
            );
        }
    }
}
