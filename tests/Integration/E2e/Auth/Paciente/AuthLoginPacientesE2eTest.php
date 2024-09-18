<?php

namespace Tests\Integration\E2e\Auth\Paciente;

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('integration_e2e_paciente_login')]
class AuthLoginPacientesE2eTest extends TestCase
{
    use DatabaseMigrations;

    public function test_deve_realizar_login_com_sucesso_para_um_medico(): void
    {
        // Arrange
        $user = User::factory()->create();
        Paciente::factory()->create([
            'user_uuid' => $user->uuid,
        ]);

        // Act
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

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'user',
        ]);
        $this->assertNotNull($response->json('access_token'));
        $this->assertEquals('Bearer', $response->json('token_type'));
        $this->assertArrayHasKey('uuid', $response->json('user'));
        $this->assertArrayHasKey('email', $response->json('user'));
        $this->assertArrayHasKey('paciente', $response->json('user'));
        $this->assertArrayHasKey('uuid', $response->json('user.paciente'));
        $this->assertArrayHasKey('nome', $response->json('user.paciente'));
        $this->assertArrayHasKey('user_uuid', $response->json('user.paciente'));
        $this->assertArrayHasKey('cpf', $response->json('user.paciente'));
    }
}
