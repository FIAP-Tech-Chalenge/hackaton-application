<?php

namespace App\Http\Controllers\Api\V1\Medicos\Horarios;

use App\Enums\StatusHorarioEnum;
use App\Http\Controllers\Controller;
use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use App\Modules\Medicos\UseCases\AlterarHorarios\AlterarHorariosUseCase;
use App\Modules\Shared\Exceptions\RegraException;
use App\Modules\Shared\Gateways\HorariosDisponiveisCommandInterface;
use App\Modules\Shared\Gateways\HorariosDisponiveisMapperInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use Throwable;

class AlterarHorarioDoDiaController extends Controller
{
    public function __construct(
        private readonly HorariosDisponiveisCommandInterface $horariosDisponiveisCommand,
        private readonly HorariosDisponiveisMapperInterface $horariosDisponiveisMapper
    ) {
    }

    public function __invoke(Request $request)
    {
        $request->validate([
            'data' => ['required', 'date_format:Y-m-d'],
            'novo_intervalo.hora_inicio' => ['required', 'date_format:H:i'],
            'novo_intervalo.hora_fim' => ['required', 'date_format:H:i'],
            'horarios_para_cancelar' => ['array'],
            'horarios_para_cancelar.*' => ['required', 'uuid'],
        ]);

        $user = Auth::user()->load('medico:uuid,user_uuid');

        $novoIntervalo = new IntervaloEntity(
            inicioDoIntervalo: Carbon::parse($request->input('novo_intervalo.hora_inicio')),
            finalDoIntervalo: Carbon::parse($request->input('novo_intervalo.hora_fim')),
            statusHorarioEnum: StatusHorarioEnum::DISPONIVEL
        );
        $novoIntervalo->setUuid(Uuid::uuid7());

        $horariosParaCancelar = $request->input('horarios_para_cancelar');
        try {
            $useCase = new AlterarHorariosUseCase(
                horariosDisponiveisCommand: $this->horariosDisponiveisCommand,
                horariosDisponiveisMapper: $this->horariosDisponiveisMapper
            );
            $agendaEntity = $useCase->execute(
                horariosParaCancelarUuids: $horariosParaCancelar,
                novoIntervalo: $novoIntervalo,
                medicoUuid: Uuid::fromString($user->medico->uuid),
                data: Carbon::parse($request->input('data'))
            );
        } catch (RegraException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            throw $e;
        }

        return response()->json(
            data: [
                'message' => 'Horários atualizados com sucesso',
                'count_horarios' => $agendaEntity->getIntervalos()->count(),
                'horarios_cancelados' => $horariosParaCancelar
            ],
            status: 201
        );
    }
}
