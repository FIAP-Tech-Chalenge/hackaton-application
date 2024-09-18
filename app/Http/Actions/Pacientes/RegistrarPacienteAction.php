<?php

namespace App\Http\Actions\Pacientes;

use App\Http\Enums\TipoUsuario;
use App\Models\Paciente;
use App\Models\User;

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
                'nome' => $nome,
                'email' => $email,
                'password' => bcrypt($password),
                'tipo' => TipoUsuario::PACIENTE->value,
            ]);

        Paciente::query()
            ->create([
                'nome' => $nome,
                'cpf' => $cpf,
                'user_id' => $user->id,
            ]);

        return $user;
    }
}
