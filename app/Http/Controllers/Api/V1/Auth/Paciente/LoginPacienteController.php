<?php

namespace App\Http\Controllers\Api\V1\Auth\Paciente;

use App\Enums\TipoUsuarioEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginPacienteController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        $user = auth()->user()->load('paciente:uuid,user_id,nome,cpf');
        $token = $user->createToken('auth_token', [TipoUsuarioEnum::PACIENTE->value]);

        return response()->json([
            'user' => $user
                ->only('uuid', 'email', 'paciente'),
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => $token->accessToken->expires_at,
        ]);
    }
}
