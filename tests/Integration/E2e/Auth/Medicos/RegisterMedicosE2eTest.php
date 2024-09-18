<?php

namespace Tests\Integration\E2e\Auth\Medicos;

use App\Enums\TipoUsuarioEnum;
use App\Models\Medico;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('integration_e2e_medico_register')]
class RegisterMedicosE2eTest extends TestCase
{
    use DatabaseMigrations;

    public function test_deve_regitrar_um_novo_medico_e_registra_lo_no_sistema_retornando_o_token(): void
    {
        // Act
        $response = $this->postJson(route('medicos.register'), [
                'nome' => 'John Doe',
                'cpf' => '123.456.789-00',
                'crm' => '123456',
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
            'tipo' => TipoUsuarioEnum::MEDICO->value,
        ]);
        $this->assertDatabaseHas('medicos', [
            'nome' => 'John Doe',
            'cpf' => '123.456.789-00',
            'crm' => '123456',
        ]);
    }

    //generate a test for cpf unique
    public function test_nao_deve_permir_registrar_um_medico_com_o_mesmo_cpf(): void
    {
        // Arrange
        $medico = Medico::factory()->create();

        // Act
        $response = $this->postJson(route('medicos.register'), [
            'nome' => 'John Doe',
            'cpf' => $medico->cpf,
            'crm' => '123456',
            'email' => 'email@email.com',
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('cpf');
    }

    //crm
    public function test_nao_deve_permir_registrar_um_medico_com_o_mesmo_crm(): void
    {
        // Arrange
        $medico = Medico::factory()->create();

        // Act
        $response = $this->postJson(route('medicos.register'), [
            'nome' => 'John Doe',
            'cpf' => '123.456.789-00',
            'crm' => $medico->crm,
            'email' => 'email@email.com',
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('crm');
    }

    //email
    public function test_nao_deve_permir_registrar_um_medico_com_o_mesmo_email(): void
    {
        // Arrange
        $medico = Medico::factory()->create();

        // Act
        $response = $this->postJson(route('medicos.register'), [
            'nome' => 'John Doe',
            'cpf' => '123.456.789-00',
            'crm' => '123456',
            'email' => $medico->user->email,
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }
}
