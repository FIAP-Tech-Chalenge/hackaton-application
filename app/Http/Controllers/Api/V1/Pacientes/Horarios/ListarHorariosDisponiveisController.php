<?php

namespace App\Http\Controllers\Api\V1\Pacientes\Horarios;

use App\Http\Controllers\Controller;
use App\Infra\Services\Pacientes\ListaHorariosDisponiveisService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class ListarHorariosDisponiveisController extends Controller
{
    public function __construct(private readonly ListaHorariosDisponiveisService $listaHorariosDisponiveisService)
    {
    }

    public function __invoke(Request $request, string $medicoUuid, string $data): JsonResponse
    {
        if (!strtotime($data)) {
            return response()->json([
                'message' => 'Data inválida',
            ], 400);
        }
        return response()->json([
            'horarios' => $this->listaHorariosDisponiveisService->execute(
                Uuid::fromString($medicoUuid),
                Carbon::parse($data)
            ),
        ]);
    }
}
