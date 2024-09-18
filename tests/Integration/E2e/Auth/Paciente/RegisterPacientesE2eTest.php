<?php

namespace Tests\Integration\E2e\Auth\Paciente;

use App\Http\Enums\TipoUsuario;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('integration_e2e_paciente_register')]
class RegisterPacienteE2ETest extends TestCase
{
    use DatabaseMigrations;

    public function test_deve_regitrar_um_novo_paciente_e_registra_lo_no_sistema_retornando_o_token(): void
    {
        // Act
        $response = $this->postJson(route('pacientes.register'), [
                'nome' => 'John Doe',
                'cpf' => '123.456.789-00',
                'email' => 'email@email.com',
                'password' => 'password',
            ]
        );

        // Assert
        $response->assertStatus(201);
        $response->assertJsonStructure(['token']);
        $response->assertJson(['token' => true]);

        $this->assertDatabaseHas('users', [
            'email' => 'email@email.com',
            'tipo' => TipoUsuario::PACIENTE->value,
        ]);
        $this->assertDatabaseHas('pacientes', [
            'nome' => 'John Doe',
            'cpf' => '123.456.789-00',
        ]);
    }

    public function test_nao_deve_permir_registrar_um_paciente_com_o_mesmo_cpf(): void
    {
        // Arrange
        $paciente = Paciente::factory()->create();

        // Act
        $response = $this->postJson(route('pacientes.register'), [
            'nome' => 'John Doe',
            'cpf' => $paciente->cpf,
            'email' => 'email@email.com',
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('cpf');
    }

    public function test_nao_deve_permir_registrar_um_paciente_com_o_mesmo_email(): void
    {
        // Arrange
        $paciente = Paciente::factory()->create();

        // Act
        $response = $this->postJson(route('pacientes.register'), [
            'nome' => 'John Doe',
            'cpf' => '123.456.789-00',
            'email' => $paciente->user->email,
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }
}
