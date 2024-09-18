<?php

namespace Tests\Integration\E2e\Pacientes\ListaDeMedicos;

use App\Enums\TipoUsuarioEnum;
use App\Models\Medico;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('integration_e2e_pacientes_listar_medicos')]
class ListaDeMedicosE2eTest extends TestCase
{
    use DatabaseMigrations;

    public function test_deve_retornar_a_lista_de_medicos_cadastrados_no_sistema(): void
    {
        // Arrange
        Medico::factory()->count(5)->create();
        $this->actingAs(User::factory()->create([
            'tipo' => TipoUsuarioEnum::PACIENTE->value,
        ]));

        // Act
        $response = $this->getJson(route('pacientes.medicos.listar'));
        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_page',
            'first_page_url',
            'from',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'data' => [
                '*' => [
                    'uuid',
                    'nome',
                    'crm',
                    'cpf',
                ],
            ],
        ]);
        $this->assertNotEmpty($response->json('data'));
        $this->assertCount(5, $response->json('data'));
    }

    public function test_nao_deve_permitir_acesso_a_lista_de_medicos_sem_autenticacao(): void
    {
        // Act
        $response = $this->getJson(route('pacientes.medicos.listar'));
        // Assert
        $response->assertStatus(401);
        $response->assertJsonStructure([
            'message',
        ]);
        $this->assertEquals('Unauthenticated.', $response->json('message'));
    }

    public function test_nao_deve_permitir_acesso_a_lista_de_medicos_com_usuario_nao_paciente(): void
    {
        // Arrange
        $user = User::factory()->create([
            'tipo' => TipoUsuarioEnum::MEDICO->value,
        ]);
        $token = $user->createToken('TestToken', [TipoUsuarioEnum::MEDICO->value]);

        // Act
        $this->actingAs($user, 'sanctum');
        $response = $this
            ->withHeader('Authorization', "Bearer $token->plainTextToken")
            ->getJson(route('pacientes.medicos.listar'));

        // Assert
        $response->assertStatus(401);
        $response->assertJsonStructure([
            'message',
        ]);
        $this->assertEquals('Unauthenticated.', $response->json('message'));
    }
}
