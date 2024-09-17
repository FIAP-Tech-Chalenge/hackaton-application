<?php

namespace App\Http\Actions\Medicos;

use App\Http\Enums\TipoUsuario;
use App\Models\Medico;
use App\Models\User;

class RegistrarMedicoAction
{
    public static function execute(
        string $nome,
        string $cpf,
        string $crm,
        string $email,
        string $password

    ): User {
        $user = User::query()
            ->create([
                'nome' => $nome,
                'email' => $email,
                'password' => bcrypt($password),
                'tipo' => TipoUsuario::MEDICO->value,
            ]);

        Medico::query()
            ->create([
                'nome' => $nome,
                'cpf' => $cpf,
                'crm' => $crm,
                'user_id' => $user->id,
            ]);

        return $user;
    }
}
