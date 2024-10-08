<?php

namespace Tests\Feature\Actions\Medicos;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\TipoUsuarioEnum;
use App\Infra\Actions\Medicos\RegistrarMedicoAction;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class RegistrarUsuarioActionTest extends TestCase
{
    use DatabaseMigrations;


    public function test_deve_salvar_o_user_e_medico_no_banco_de_dados(): void
    {
        // Act
        $user = RegistrarMedicoAction::execute(
            nome: 'Nome',
            cpf: '12345678900',
            crm: '123456',
            email: 'email@email',
            password: 12345678
        );

        // Assert
        $this->assertDatabaseHas('users', [
            'email' => 'email@email',
            'tipo' => TipoUsuarioEnum::MEDICO->value,
        ]);

        $this->assertDatabaseHas('medicos', [
            'nome' => 'Nome',
            'cpf' => '12345678900',
            'crm' => '123456',
            'user_id' => $user->id,
        ]);
    }

}
