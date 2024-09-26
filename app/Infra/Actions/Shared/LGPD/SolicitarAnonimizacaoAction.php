<?php

namespace App\Infra\Actions\Shared\LGPD;

use App\Enums\StatusAnonimizacaoEnum;
use App\Models\SolicitacaoAnonimizacao;
use App\Models\User;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class SolicitarAnonimizacaoAction
{
    public static function execute(User $user, Carbon $agendamento): string
    {
        $solicitacao = SolicitacaoAnonimizacao::query()
            ->create([
                'uuid' => Uuid::uuid7()->toString(),
                'user_id' => $user->id,
                'data_solicitacao' => Carbon::now(),
                'data_anonimizacao' => $agendamento,
                'status' => StatusAnonimizacaoEnum::AGENDADO->value,
                'job_id' => $user->uuid,
            ]);

        return $solicitacao->uuid;
    }
}
