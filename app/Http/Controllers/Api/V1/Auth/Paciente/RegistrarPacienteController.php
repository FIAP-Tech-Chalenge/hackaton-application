<?php

namespace App\Http\Controllers\Api\V1\Auth\Paciente;

use App\Enums\TipoUsuarioEnum;
use App\Http\Controllers\Controller;
use App\Infra\Actions\Pacientes\RegistrarPacienteAction;
use Illuminate\Http\Request;

class RegistrarPacienteController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'nome' => ['required'],
            'cpf' => ['required', 'unique:pacientes,cpf', 'regex:/^\d+$/'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6'],
        ]);

        $user = RegistrarPacienteAction::execute(
            nome: $request->nome,
            cpf: $request->cpf,
            email: $request->email,
            password: $request->password
        );

        $token = $user->createToken(TipoUsuarioEnum::PACIENTE->value)->plainTextToken;

        return response()->json(['token' => $token], 201);
    }
}
