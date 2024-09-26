<?php

namespace Tests\Integration\E2e\Pacientes\Agendamentos;

use App\Enums\StatusHorarioEnum;
use App\Enums\TipoUsuarioEnum;
use App\Jobs\Agendamento\ValidacaoDeReservaJob;
use App\Models\HorarioDisponivel;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('integration_e2e_reservar_horario')]
class ReservarHorarioE2eTest extends TestCase
{
    use DatabaseMigrations;

    public function test_deve_reservar_horario()
    {
        // Arrange
        $user = User::factory()->create([
            'tipo' => TipoUsuarioEnum::PACIENTE->value,
        ]);
        $paciente = Paciente::factory()->create([
            'user_id' => $user->id,
        ]);
        $horarioDisponivel = HorarioDisponivel::factory()->create();
        Sanctum::actingAs($user, ['paciente']);
        Queue::fake();

        // Act
        $response = $this->postJson(
            route('pacientes.agendamentos.reservar'),
            [
                'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            ],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        );

        // Assert
        $response->assertCreated();
        $this->assertDatabaseHas('paciente_agendamentos', [
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $paciente->uuid,
        ]);
        $this->assertDatabaseHas('horarios_disponiveis', [
            'uuid' => $horarioDisponivel->uuid,
            'status' => StatusHorarioEnum::RESERVADO->value,
        ]);
        $response->assertJson([
            'message' => 'Horário solicitado, você receberá um e-mail com o retorno do agendamento.',
        ]);
        $response->assertJsonStructure([
            'message',
        ]);

        Queue::assertPushed(ValidacaoDeReservaJob::class);
        Queue::assertCount(1);
    }

    public function test_deve_lidar_quando_mais_de_uma_solicitacao_para_o_mesmo_horario_ocorrer()
    {
        // Arrange
        $user = User::factory()->create([
            'tipo' => TipoUsuarioEnum::PACIENTE->value,
        ]);
        $paciente = Paciente::factory()->create([
            'user_id' => $user->id,
        ]);
        $horarioDisponivel = HorarioDisponivel::factory()->create([
            'status' => StatusHorarioEnum::DISPONIVEL->value,
        ]);
        Sanctum::actingAs($user, ['paciente']);
        Queue::fake();

        // Act
        for ($i = 0; $i < 3; $i++) {
            $responses[] = $this->postJson(
                route('pacientes.agendamentos.reservar'),
                [
                    'horario_disponivel_uuid' => $horarioDisponivel->uuid,
                ],
                [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ]
            );
        }

        // Assert
        // Verifica se apenas uma requisição foi bem-sucedida
        $successfulResponses = array_filter($responses, function ($response) {
            return $response->status() === 201;
        });

        $this->assertCount(1, $successfulResponses);

        // Verifica se as outras duas requisições falharam
        $failedResponses = array_filter($responses, function ($response) {
            return $response->status() !== 201;
        });
        $this->assertCount(2, $failedResponses);
    }
}
