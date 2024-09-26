<?php

namespace Tests\Integration\E2e\LGPD;

use App\Enums\TipoUsuarioEnum;
use App\Jobs\LGPD\SolicitarAnonimizacaoScheduleJob;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('integration_e2e_lgpd_licitacao')]
class SolicitarAnonimizacaoE2eTest extends TestCase
{
    use DatabaseMigrations;

    public function test_deve_realizar_a_solicitacao_agendar_o_job_e_anonimizar_o_usuario_medico()
    {
        // Arrange
        Queue::fake();
        $user = User::factory()->create([
            'tipo' => TipoUsuarioEnum::MEDICO->value,
        ]);
        Sanctum::actingAs($user, [TipoUsuarioEnum::MEDICO->value]);

        // Act
        $response = $this->postJson(
            route('medicos.lgpd.solicitar-anonimizacao'),
            [],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        );

        // Assert
        $response->assertOk();
        $response->assertJson([
            'message' => 'Solicitação de anonimização realizada com sucesso. Efetivaremos a anonimização dos seus dados em 7 dias.',
            'user' => [
                'uuid' => $user->uuid,
                'email' => $user->email,
            ],
            'protocolo_anonimizacao' => $response->json('protocolo_anonimizacao'),
        ]);

        Queue::assertPushed(SolicitarAnonimizacaoScheduleJob::class);
        Queue::assertCount(1);
        Queue::assertPushed(
            SolicitarAnonimizacaoScheduleJob::class,
            function (SolicitarAnonimizacaoScheduleJob $job) {
                return $job->delay == now()->startOfDay()->addDays(7);
            }
        );
        $this->assertDatabaseHas('solicitacao_anonimizacoes', [
            'user_id' => $user->id,
            'data_anonimizacao' => now()->startOfDay()->addDays(7),
            'uuid' => $response->json('protocolo_anonimizacao'),
        ]);
    }

    public function test_deve_realizar_a_solicitacao_agendar_o_job_e_anonimizar_o_usuario_paciente()
    {
        // Arrange
        Queue::fake();
        $user = User::factory()->create([
            'tipo' => TipoUsuarioEnum::PACIENTE->value,
        ]);
        Sanctum::actingAs($user, [TipoUsuarioEnum::PACIENTE->value]);

        // Act
        $response = $this->postJson(
            route('pacientes.lgpd.solicitar-anonimizacao'),
            [],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        );

        // Assert
        $response->assertOk();
        $response->assertJson([
            'message' => 'Solicitação de anonimização realizada com sucesso. Efetivaremos a anonimização dos seus dados em 7 dias.',
            'user' => [
                'uuid' => $user->uuid,
                'email' => $user->email,
            ],
            'protocolo_anonimizacao' => $response->json('protocolo_anonimizacao'),
        ]);

        Queue::assertPushed(SolicitarAnonimizacaoScheduleJob::class);
        Queue::assertCount(1);
        Queue::assertPushed(
            SolicitarAnonimizacaoScheduleJob::class,
            function (SolicitarAnonimizacaoScheduleJob $job) {
                return $job->delay == now()->startOfDay()->addDays(7);
            }
        );
        $this->assertDatabaseHas('solicitacao_anonimizacoes', [
            'user_id' => $user->id,
            'data_anonimizacao' => now()->startOfDay()->addDays(7),
            'uuid' => $response->json('protocolo_anonimizacao'),
        ]);
    }
}
