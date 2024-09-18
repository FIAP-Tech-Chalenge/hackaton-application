<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Enums\TipoUsuario;
use Illuminate\Http\Request;

class LoginMedicoController extends Controller
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

        $user = auth()->user()->load('medico:id,user_id,crm,cpf');
        $token = $user->createToken('auth_token', [TipoUsuario::MEDICO->value]);
        
        return response()->json([
            'user' => $user
                ->only('id', 'name', 'email', 'medico'),
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => $token->accessToken->expires_at,
        ]);
    }
}
