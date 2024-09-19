<?php

namespace App\Modules\Shared\Entities;

use Ramsey\Uuid\UuidInterface;

readonly class MedicoEntity
{
    public function __construct(
        public UuidInterface $uuid,
        public string $nome,
        public string $crm,
        public string $email
    ) {
    }
}
