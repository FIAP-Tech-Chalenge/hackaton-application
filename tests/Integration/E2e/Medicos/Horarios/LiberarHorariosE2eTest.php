<?php

namespace Tests\Integration\E2e\Medicos\Horarios;

use App\Enums\IntervalosDeAgendamentosEnum;
use App\Enums\StatusHorarioEnum;
use App\Enums\TipoUsuarioEnum;
use App\Models\HorarioDisponivel;
use App\Models\Medico;
use App\Models\User;
use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use App\Modules\Medicos\Entities\Horarios\PeriodoAtendimento;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

#[Group('integration_e2e_liberar_horarios')]
class LiberarHorariosE2eTest extends TestCase
{
    use DatabaseMigrations;


    public function test_deve_gerar_horarios_disponiveis()
    {
        // Arrange
        $user = User::factory()->create([
            'tipo' => TipoUsuarioEnum::MEDICO->value,
        ]);
        $this->actingAs($user,);

        Medico::factory()->create([
            'user_id' => $user->id,
        ]);

        $dataDaAgenda = now()->startOfDay();

        // Act
        $response = $this->postJson(
            route('medicos.horarios.liberar-agenda-do-dia'),
            [
                'data' => $dataDaAgenda->format('Y-m-d'),
                'periodo_atendimento' => [
                    'hora_inicio' => '08:00',
                    'hora_fim' => '18:00',
                ],
                'intervalos_indisponiveis' => [
                    [
                        'hora_inicio' => '12:00',
                        'hora_fim' => '14:00',
                    ],
                ],
                'intervalo_por_agendamento' => IntervalosDeAgendamentosEnum::SESSENTA_MINUTOS->value,
            ],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        );

        // Assert
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'count_horarios',
        ]);
    }

    #[Group('integration_e2e_liberar_horarios_2')]
    public function test_deve_gerar_erro_quando_medico_ja_tem_agenda()
    {
        // Arrange
        $user = User::factory()->create([
            'tipo' => TipoUsuarioEnum::MEDICO->value,
        ]);
        Sanctum::actingAs($user, [TipoUsuarioEnum::MEDICO->value]);

        $medico = Medico::factory()->create([
            'user_id' => $user->id,
        ]);

        $dataDaAgenda = now()->startOfDay();
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
                'medico_uuid' => $medico->uuid,
                'data' => $dataDaAgenda->format('Y-m-d'),
                'hora_inicio' => $periodo->inicioDoIntervalo->format('Y-m-d H:i'),
                'hora_fim' => $periodo->finalDoIntervalo->format('Y-m-d H:i'),
                'status' => StatusHorarioEnum::DISPONIVEL->value,
            ]);
        }

        // Act
        $response = $this->postJson(
            route('medicos.horarios.liberar-agenda-do-dia'),
            [
                'data' => $dataDaAgenda->format('Y-m-d'),
                'periodo_atendimento' => [
                    'hora_inicio' => '08:00',
                    'hora_fim' => '18:00',
                ],
                'intervalos_indisponiveis' => [
                    [
                        'hora_inicio' => '12:00',
                        'hora_fim' => '14:00',
                    ],
                ],
                'intervalo_por_agendamento' => IntervalosDeAgendamentosEnum::SESSENTA_MINUTOS->value,
            ],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        );

        // Assert
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
        ]);
    }
}
