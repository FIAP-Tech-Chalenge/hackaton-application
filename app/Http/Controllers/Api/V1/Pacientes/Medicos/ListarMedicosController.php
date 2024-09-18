<?php

namespace App\Http\Controllers\Api\V1\Pacientes\Medicos;

use App\Http\Controllers\Controller;
use App\Infra\Services\Pacientes\ListaDeMedicosService;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class ListarMedicosController extends Controller
{
    public function __construct(private readonly ListaDeMedicosService $listaDeMedicosService)
    {
    }

    public function __invoke(Request $request): Paginator
    {
        return $this->listaDeMedicosService->execute();
    }
}
