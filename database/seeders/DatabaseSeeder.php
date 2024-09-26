<?php

namespace Database\Seeders;

use App\Enums\IntervalosDeAgendamentosEnum;
use App\Enums\StatusHorarioEnum;
use App\Enums\TipoUsuarioEnum;
use App\Models\HorarioDisponivel;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\PacienteAgendamento;
use App\Models\User;
use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use App\Modules\Medicos\Entities\Horarios\PeriodoAtendimento;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use App\Modules\Shared\Exceptions\Horarios\HoraInicialMaiorQueFinalException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Uid\Ulid;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    private Model $medico;
    private Model $paciente;

    /**
     * @throws HoraInicialMaiorQueFinalException
     */
    public function run(): void
    {
        $this->medicoFactory();
        $this->pacienteFactory();
    }

    /**
     * @throws HoraInicialMaiorQueFinalException
     */
    private function medicoFactory(): void
    {
        $user = User::factory()->create([
            'tipo' => TipoUsuarioEnum::MEDICO->value,
        ]);
        $this->medico = Medico::factory()->create([
            'user_id' => $user->id,
        ]);
        $this->montarHorariosDisponiveis(now()->startOfDay());
    }

    private function pacienteFactory(): void
    {
        $user = User::factory()->create([
            'tipo' => TipoUsuarioEnum::PACIENTE->value,
        ]);
        $this->paciente = Paciente::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->atribuirPacienteAoHorarioDisponivel();
    }

    /**
     * @throws HoraInicialMaiorQueFinalException
     */
    private function montarHorariosDisponiveis(Carbon $dataDoAgendamento): void
    {
        $intervalosIndisponiveis = new IntervalosCollection();
        $intervalosIndisponiveis->add(
            new IntervaloEntity(
                inicioDoIntervalo: Carbon::parse('12:00'),
                finalDoIntervalo: Carbon::parse('14:00'),
                statusHorarioEnum: StatusHorarioEnum::INDISPONIVEL
            )
        );

        $periodoAtendimento = new PeriodoAtendimento(Carbon::parse('08:00'), Carbon::parse('18:00'));

        $periodos = $periodoAtendimento->montarAgendaDoDia(
            intervalosDeAgendamentosEnum: IntervalosDeAgendamentosEnum::SESSENTA_MINUTOS,
            intervalosIndisponiveis: $intervalosIndisponiveis
        );

        foreach ($periodos as $periodo) {
            HorarioDisponivel::factory()->create([
                'uuid' => Uuid::uuid7()->toString(),
                'medico_uuid' => $this->medico->uuid,
                'data' => $dataDoAgendamento,
                'hora_inicio' => $periodo->inicioDoIntervalo->format('H:i'),
                'hora_fim' => $periodo->finalDoIntervalo->format('H:i'),
                'status' => $periodo->statusHorarioEnum->value,
            ]);
        }
    }

    private function atribuirPacienteAoHorarioDisponivel(): void
    {
        $horarioDisponivel = HorarioDisponivel::query()
            ->where('status', '=', StatusHorarioEnum::DISPONIVEL->value)
            ->where('medico_uuid', '=', $this->medico->uuid)
            ->first();

        PacienteAgendamento::factory()->create([
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $this->paciente->uuid,
            'assinatura_confirmacao' => Ulid::generate(),
        ]);
        $horarioDisponivel->status = StatusHorarioEnum::DISPONIVEL->value;
        $horarioDisponivel->save();
    }
}
