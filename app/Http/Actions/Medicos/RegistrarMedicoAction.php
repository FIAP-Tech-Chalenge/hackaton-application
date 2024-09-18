<?php

namespace App\Http\Actions\Medicos;

use App\Http\Enums\TipoUsuario;
use App\Models\Medico;
use App\Models\User;
use Ramsey\Uuid\Uuid;

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
                'uuid' => Uuid::uuid7()->toString(),
                'nome' => $nome,
                'email' => $email,
                'password' => bcrypt($password),
                'tipo' => TipoUsuario::MEDICO->value,
            ]);
        
        Medico::query()
            ->create([
                'uuid' => Uuid::uuid7()->toString(),
                'nome' => $nome,
                'cpf' => $cpf,
                'crm' => $crm,
                'user_uuid' => $user->uuid,
            ]);

        return $user;
    }
}
