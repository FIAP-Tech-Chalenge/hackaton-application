<?php

namespace App\Modules\Pacientes\Entities;

use Carbon\Carbon;
use Ramsey\Uuid\UuidInterface;

readonly class ReservaEntity
{
    public function __construct(
        public UuidInterface $horarioDisponivelUuid,
        public UuidInterface $pacienteUuid,
        public string $assinaturaConfirmacao,
        public ?Carbon $confirmadoEm = null,
    ) {
    }
}
