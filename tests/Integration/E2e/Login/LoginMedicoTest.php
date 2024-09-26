<?php

namespace Tests\Integration\E2e\Login;

use App\Models\Medico;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('integration_login_medico')]
class LoginMedicoTest extends TestCase
{
    use DatabaseMigrations;

    public function test_deve_realizar_login_com_sucesso_para_um_medico(): void
    {
        // Arrange
        $user = User::factory()->create();
        Medico::factory()->create([
            'user_id' => $user->id,
        ]);

        // Act
        $response = $this->postJson(
            route('medicos.login'),
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
        $this->assertArrayHasKey('medico', $response->json('user'));
        $this->assertArrayHasKey('uuid', $response->json('user.medico'));
        $this->assertArrayHasKey('user_id', $response->json('user.medico'));
        $this->assertArrayHasKey('nome', $response->json('user.medico'));
        $this->assertArrayHasKey('crm', $response->json('user.medico'));
        $this->assertArrayHasKey('cpf', $response->json('user.medico'));
    }

    public function test_deve_falhar_login_com_credenciais_invalidas(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->postJson(
            route('medicos.login'),
            [
                'email' => $user->email,
                'password' => 'wrongpassword',
            ],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        );

        // Assert
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Credenciais inválidas.',
        ]);
    }
}
