<?php

namespace App\Http\Controllers\Api\V1\Auth\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutPacienteController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->user()->tokens();
        $token->delete();

        return response()->json([
            'message' => 'Deslogado com sucesso.',
        ]);
    }
}
