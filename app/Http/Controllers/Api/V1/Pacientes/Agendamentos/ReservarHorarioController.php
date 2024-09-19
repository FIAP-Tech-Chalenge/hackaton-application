<?php

namespace App\Http\Controllers\Api\V1\Pacientes\Agendamentos;

use App\Http\Controllers\Controller;
use App\Jobs\Agendamento\ValidacaoDeReservaJob;
use App\Modules\Pacientes\UseCases\ReservarHorarioUseCase;
use App\Modules\Shared\Exceptions\RegraException;
use App\Modules\Shared\Gateways\HorariosDisponiveisMapperInterface;
use App\Modules\Shared\Gateways\PacienteMapperInterface;
use App\Modules\Shared\Gateways\ReservarHorarioCommandInterface;
use App\Modules\Shared\Gateways\ReservarHorarioMapperInterface;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Throwable;

class ReservarHorarioController extends Controller
{
    public function __construct(
        private readonly HorariosDisponiveisMapperInterface $horariosDisponiveisMapper,
        private readonly ReservarHorarioCommandInterface $reservarHorarioCommand,
        private readonly PacienteMapperInterface $pacienteMapper,
        private readonly ReservarHorarioMapperInterface $reservarHorarioMapper
    ) {
    }

    public function __invoke(Request $request)
    {
        $request->validate([
            'horario_disponivel_uuid' => ['required', 'uuid'],
        ]);
        $paciente = $request->user()->load('paciente:uuid,user_uuid');

        try {
            $useCase = new ReservarHorarioUseCase(
                horariosDisponiveisMapper: $this->horariosDisponiveisMapper,
                reservarHorarioCommand: $this->reservarHorarioCommand,
                pacienteMapper: $this->pacienteMapper
            );
            $reservaEntity = $useCase->execute(
                horarioDisponivelUuid: Uuid::fromString($request->input('horario_disponivel_uuid')),
                pacienteUuid: Uuid::fromString($paciente->paciente->uuid)
            );

            ValidacaoDeReservaJob::dispatch(
                $reservaEntity,
                $this->reservarHorarioCommand,
                $this->reservarHorarioMapper
            )
                ->delay(now()->addMinutes(10));
        } catch (RegraException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        } catch (Throwable $e) {
            throw $e;
        }

        return response()->json([
            'message' => 'Horário reservado com sucesso, você receberá um e-mail de confirmação.'
        ], 201);
    }
}
