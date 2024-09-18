<?php

namespace Tests\Integration\E2e\Auth\Paciente;

use App\Models\Medico;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('integration_e2e_paciente_logout')]
class AuthLogoutPacientesE2eTest extends TestCase
{
    use DatabaseMigrations;

    public function test_deve_realizar_logout_com_sucesso_para_um_medico(): void
    {
        // Arrange
        $user = User::factory()->create();

        Medico::factory()->create([
            'user_uuid' => $user->uuid,
        ]);

        $response = $this->postJson(
            route('pacientes.login'),
            [
                'email' => $user->email,
                'password' => 'password',
            ],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        );

        $token = $response->json('access_token');

        // Act
        $response = $this->postJson(
            route('pacientes.logout'),
            [],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer $token",
            ]
        );

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
        ]);
        $this->assertEquals('Deslogado com sucesso.', $response->json('message'));
        $this->assertDatabaseHas('users', [
            'uuid' => $user->uuid,
        ]);
    }
}
