<?php

namespace App\Infra\Database\Mappers\Pacientes;

use App\Models\Paciente;
use App\Modules\Shared\Entities\PacienteEntity;
use App\Modules\Shared\Gateways\Pacientes\PacienteMapperInterface;
use Ramsey\Uuid\UuidInterface;

class PacienteMapper implements PacienteMapperInterface
{

    public function getPaciente(UuidInterface $uuid): ?PacienteEntity
    {
        $paciente = Paciente::query()
            ->select('uuid', 'nome', 'cpf', 'user_id')
            ->where('uuid', '=', $uuid->toString())
            ->with('user:id,email,uuid')
            ->first();
        if (!$paciente) {
            return null;
        }

        return new PacienteEntity(
            uuid: $uuid,
            nome: $paciente->nome,
            cpf: $paciente->cpf,
            email: $paciente->user->email
        );
    }
}
