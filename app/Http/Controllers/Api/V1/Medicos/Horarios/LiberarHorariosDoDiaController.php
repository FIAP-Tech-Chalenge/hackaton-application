<?php

namespace App\Http\Controllers\Api\V1\Medicos\Horarios;

use App\Enums\IntervalosDeAgendamentosEnum;
use App\Enums\StatusHorarioEnum;
use App\Http\Controllers\Controller;
use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use App\Modules\Medicos\Entities\Horarios\PeriodoAtendimento;
use App\Modules\Medicos\UseCases\LiberarHorarios\LiberarHorariosUseCase;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use App\Modules\Shared\Gateways\Horarios\HorariosDisponiveisCommandInterface;
use App\Modules\Shared\Gateways\Horarios\HorariosDisponiveisMapperInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use LogicException;
use Ramsey\Uuid\Uuid;
use Throwable;

class LiberarHorariosDoDiaController extends Controller
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
            'periodo_atendimento.hora_inicio' => ['required', 'date_format:H:i'],
            'periodo_atendimento.hora_fim' => ['required', 'date_format:H:i'],
            'intervalos_indisponiveis' => ['array'],
            'intervalos_indisponiveis.*.hora_inicio' => ['required', 'date_format:H:i'],
            'intervalos_indisponiveis.*.hora_fim' => ['required', 'date_format:H:i'],
            'intervalo_por_agendamento' => ['required', new Enum(IntervalosDeAgendamentosEnum::class)],
        ]);
        $user = Auth::user()->load('medico:uuid,user_uuid');

        try {
            DB::beginTransaction();
            $periodoDeAtendimento = new PeriodoAtendimento(
                Carbon::parse($request->input('periodo_atendimento.hora_inicio')),
                Carbon::parse($request->input('periodo_atendimento.hora_fim'))
            );

            $intervalosIndisponiveis = new IntervalosCollection();
            foreach ($request->input('intervalos_indisponiveis') as $intervalo) {
                $intervalosIndisponiveis->add(
                    intervalo: new IntervaloEntity(
                        inicioDoIntervalo: Carbon::parse($intervalo['hora_inicio']),
                        finalDoIntervalo: Carbon::parse($intervalo['hora_fim']),
                        statusHorarioEnum: StatusHorarioEnum::DISPONIVEL
                    )
                );
            }

            $useCase = new LiberarHorariosUseCase(
                horariosDisponiveisCommand: $this->horariosDisponiveisCommand,
                horariosDisponiveisMapper: $this->horariosDisponiveisMapper
            );
            $agendaEntity = $useCase->execute(
                periodoAtendimento: $periodoDeAtendimento,
                intervalo: IntervalosDeAgendamentosEnum::from($request->input('intervalo_por_agendamento')),
                intervalosIndisponiveis: $intervalosIndisponiveis,
                medicoUuid: Uuid::fromString($user->medico->uuid),
                data: Carbon::parse($request->input('data'))
            );
            DB::commit();
        } catch (LogicException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json(
            data: [
                'message' => 'Horários liberados com sucesso',
                'count_horarios' => $agendaEntity->getIntervalos()->count()
            ],
            status: 201
        );
    }
}
