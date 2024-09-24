<?php

namespace Tests\Integration\E2e\Auth\Medicos;

use App\Models\Medico;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('integration_e2e_medico_logout')]
class AuthLogoutMedicosE2eTest extends TestCase
{
    use DatabaseMigrations;

    public function test_deve_realizar_logout_com_sucesso_para_um_medico(): void
    {
        // Arrange
        $user = User::factory()->create();

        Medico::factory()->create([
            'user_id' => $user->id,
        ]);

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

        $token = $response->json('access_token');

        // Act
        $response = $this->postJson(
            route('medicos.logout'),
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
