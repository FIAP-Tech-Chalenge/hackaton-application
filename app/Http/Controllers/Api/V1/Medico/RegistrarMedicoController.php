<?php

namespace App\Http\Controllers\Api\V1\Medico;

use App\Http\Actions\Medicos\RegistrarMedicoAction;
use App\Http\Controllers\Controller;
use App\Http\Enums\TipoUsuario;
use Illuminate\Http\Request;

class RegistrarMedicoController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'nome' => ['required'],
            'cpf' => ['required', 'unique:medicos,cpf'],
            'crm' => ['required', 'unique:medicos,crm'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6'],
        ]);

        $user = RegistrarMedicoAction::execute(
            nome: $request->nome,
            cpf: $request->cpf,
            crm: $request->crm,
            email: $request->email,
            password: $request->password,
            tipo: TipoUsuario::MEDICO,
        );

        $token = $user->createToken('medico')->plainTextToken;

        return response()->json(['token' => $token], 201);
    }
}
