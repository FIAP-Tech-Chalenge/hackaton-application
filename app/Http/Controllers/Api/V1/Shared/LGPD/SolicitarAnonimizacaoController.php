<?php

namespace App\Http\Controllers\Api\V1\Shared\LGPD;

use App\Http\Controllers\Controller;
use App\Infra\Actions\Shared\LGPD\SolicitarAnonimizacaoAction;
use App\Jobs\LGPD\SolicitarAnonimizacaoScheduleJob;
use Illuminate\Http\JsonResponse;

class SolicitarAnonimizacaoController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $user = auth()->user();
        $dataAgendamento = now()->startOfDay()->addDays(7);
        SolicitarAnonimizacaoScheduleJob::dispatch($user)
            ->delay($dataAgendamento);
        $protocolo = SolicitarAnonimizacaoAction::execute(
            $user,
            $dataAgendamento
        );

        return response()->json(
            [
                'message' => 'Solicitação de anonimização realizada com sucesso. Efetivaremos a anonimização dos seus dados em 7 dias.',
                'user' => $user->only('uuid', 'email'),
                'protocolo_anonimizacao' => $protocolo,
            ]
        );
    }
}
