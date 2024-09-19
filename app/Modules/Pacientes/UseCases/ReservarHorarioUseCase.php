<?php

namespace App\Modules\Pacientes\UseCases;

use App\Enums\StatusHorarioEnum;
use App\Modules\Pacientes\Entities\ReservaEntity;
use App\Modules\Shared\Exceptions\Horarios\HorarioNaoDisponivelException;
use App\Modules\Shared\Exceptions\Horarios\HorarioNaoEncontradoException;
use App\Modules\Shared\Exceptions\Horarios\PacienteNaoEncontradoException;
use App\Modules\Shared\Gateways\HorariosDisponiveisMapperInterface;
use App\Modules\Shared\Gateways\PacienteMapperInterface;
use App\Modules\Shared\Gateways\ReservarHorarioCommandInterface;
use Ramsey\Uuid\UuidInterface;

readonly class ReservarHorarioUseCase
{
    public function __construct(
        private HorariosDisponiveisMapperInterface $horariosDisponiveisMapper,
        private ReservarHorarioCommandInterface $reservarHorarioCommand,
        private PacienteMapperInterface $pacienteMapper
    ) {
    }

    /**
     * @throws HorarioNaoEncontradoException
     * @throws HorarioNaoDisponivelException
     * @throws PacienteNaoEncontradoException
     */
    public function execute(UuidInterface $horarioDisponivelUuid, UuidInterface $pacienteUuid): ReservaEntity
    {
        $pacienteEntity = $this->pacienteMapper->getPaciente($pacienteUuid);
        if (!$pacienteEntity) {
            throw new PacienteNaoEncontradoException('Paciente não encontrado', 404);
        }

        $horarioEntity = $this->horariosDisponiveisMapper->getHorarioDisponivel($horarioDisponivelUuid);
        if (!$horarioEntity) {
            throw new HorarioNaoEncontradoException('Horário não encontrado', 404);
        }
        if ($horarioEntity->status !== StatusHorarioEnum::DISPONIVEL) {
            throw new HorarioNaoDisponivelException('Horário não disponível', 400);
        }

        return $this->reservarHorarioCommand->reservarHorario($horarioEntity, $pacienteUuid);
    }

}
