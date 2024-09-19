<?php

namespace App\Modules\Shared\Gateways;

use App\Modules\Shared\Entities\PacienteEntity;
use Ramsey\Uuid\UuidInterface;

interface PacienteMapperInterface
{
    public function getPaciente(UuidInterface $uuid): ?PacienteEntity;
}
