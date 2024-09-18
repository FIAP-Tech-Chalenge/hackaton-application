<?php

namespace Tests\Feature\Actions\Pacientes;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Actions\Pacientes\RegistrarPacienteAction;
use App\Http\Enums\TipoUsuario;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class RegistrarUsuarioActionTest extends TestCase
{
    use DatabaseMigrations;


    public function test_deve_salvar_o_user_e_medico_no_banco_de_dados(): void
    {
        // Act
        $user = RegistrarPacienteAction::execute(
            nome: 'Nome',
            cpf: '123.456.789-00',
            email: 'email@email',
            password: 12345678
        );

        // Assert
        $this->assertDatabaseHas('users', [
            'email' => 'email@email',
            'tipo' => TipoUsuario::PACIENTE->value,
        ]);

        $this->assertDatabaseHas('pacientes', [
            'nome' => 'Nome',
            'cpf' => '123.456.789-00',
            'user_id' => $user->id,
        ]);
    }

}
