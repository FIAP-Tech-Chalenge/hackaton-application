<?php

namespace App\Jobs\LGPD;

use App\Enums\StatusAnonimizacaoEnum;
use App\Models\SolicitacaoAnonimizacao;
use App\Models\User;
use App\Notifications\LGPD\SolicitacaoDeAnonimizacaoRealizadaMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SolicitarAnonimizacaoScheduleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    public static string $maskEmail = '*****@*****.***';
    public static string $maskCpf = '***.***.***-**';
    public static string $maskNome = '*****';
    public static string $maskCrm = '*****';

    public function __construct(private readonly User $user)
    {
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $this->anonimizarCliente();
            SolicitacaoAnonimizacao::query()
                ->where('user_id', $this->user->id)
                ->update([
                    'status' => StatusAnonimizacaoEnum::ANONIMIZADO->value,
                ]);
            $this->user->notify(new SolicitacaoDeAnonimizacaoRealizadaMail());
        });
    }

    private function anonimizarCliente(): void
    {
        $this->user->update([
            'email' => static::$maskEmail,
            'password' => '',
        ]);


        if ($this->user->paciente) {
            $this->user->paciente->update([
                'nome' => static::$maskNome,
                'cpf' => static::$maskCpf,
            ]);
        }

        if ($this->user->medico) {
            $this->user->medico->update([
                'nome' => static::$maskNome,
                'cpf' => static::$maskCpf,
                'crm' => static::$maskCrm,
            ]);
        }
    }
}
