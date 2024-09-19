<?php

namespace App\Modules\Shared\Entities;

use Ramsey\Uuid\UuidInterface;

readonly class PacienteEntity
{
    public function __construct(
        public UuidInterface $uuid,
        public string $nome,
        public string $cpf,
        public string $email
    ) {
    }
}
