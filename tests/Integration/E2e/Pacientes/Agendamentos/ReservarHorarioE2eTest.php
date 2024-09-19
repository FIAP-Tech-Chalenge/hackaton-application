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
            'user_uuid' => $user->uuid,
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
        $this->assertDatabaseHas('paciente_horarios_disponiveis', [
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $paciente->uuid,
        ]);
        $this->assertDatabaseHas('horarios_disponiveis', [
            'uuid' => $horarioDisponivel->uuid,
            'status' => StatusHorarioEnum::RESERVADO->value,
        ]);
        $response->assertJson([
            'message' => 'Horário reservado com sucesso, você receberá um e-mail de confirmação.',
        ]);
        $response->assertJsonStructure([
            'message',
        ]);

        Queue::assertPushed(ValidacaoDeReservaJob::class);
        Queue::assertCount(1);
    }
}
