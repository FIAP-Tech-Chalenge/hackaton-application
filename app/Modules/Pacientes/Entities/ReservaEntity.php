<?php

namespace App\Modules\Pacientes\Entities;

use Ramsey\Uuid\UuidInterface;

readonly class ReservaEntity
{
    public function __construct(
        public UuidInterface $horarioDisponivelUuid,
        public UuidInterface $pacienteUuid,
        public string $assinaturaConfirmacao
    ) {
    }
}
