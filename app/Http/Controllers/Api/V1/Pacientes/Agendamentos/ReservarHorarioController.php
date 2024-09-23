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
use Illuminate\Support\Facades\DB;
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
            DB::beginTransaction();
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
                ->delay(now()->addSeconds(10));
            DB::commit();
        } catch (RegraException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'message' => 'Horário solicitado, você receberá um e-mail com o retorno do agendamento.'
        ], 201);
    }
}
