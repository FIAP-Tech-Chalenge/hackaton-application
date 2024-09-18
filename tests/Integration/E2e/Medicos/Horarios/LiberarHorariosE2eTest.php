<?php

namespace Tests\Integration\E2e\Medicos\Horarios;

use App\Enums\IntervalosDeAgendamentosEnum;
use App\Enums\TipoUsuarioEnum;
use App\Models\Medico;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Group;
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
            'user_uuid' => $user->uuid,
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
}
