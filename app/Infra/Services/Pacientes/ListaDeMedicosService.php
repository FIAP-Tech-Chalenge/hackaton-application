<?php

namespace App\Infra\Services\Pacientes;

use App\Models\Medico;
use Illuminate\Contracts\Pagination\Paginator;

class ListaDeMedicosService
{
    public function execute(int $perPage = 15): Paginator
    {
        return Medico::query()
            ->select('uuid', 'nome', 'crm')
            ->simplePaginate($perPage);
    }
}
