<?php

namespace App\Infra\Actions\Pacientes;

use App\Enums\TipoUsuarioEnum;
use App\Models\Paciente;
use App\Models\User;
use Ramsey\Uuid\Uuid;

class RegistrarPacienteAction
{
    public static function execute(
        string $nome,
        string $cpf,
        string $email,
        string $password
    ): User {
        $user = User::query()
            ->create([
                'uuid' => Uuid::uuid7()->toString(),
                'nome' => $nome,
                'email' => $email,
                'password' => bcrypt($password),
                'tipo' => TipoUsuarioEnum::PACIENTE->value,
            ]);
        Paciente::query()
            ->create([
                'uuid' => Uuid::uuid7()->toString(),
                'nome' => $nome,
                'cpf' => $cpf,
                'user_uuid' => $user->uuid,
            ]);

        return $user;
    }
}
