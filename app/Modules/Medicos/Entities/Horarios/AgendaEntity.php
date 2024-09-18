<?php

namespace App\Modules\Medicos\Entities\Horarios;

use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use Ramsey\Uuid\UuidInterface;

class AgendaEntity
{
    private IntervalosCollection $intervalos;
    private UuidInterface $medicoUuid;

    private function __construct()
    {
    }

    public static function makeNovaAgendaDoDia(IntervalosCollection $intervalos, UuidInterface $medicoUuid): self
    {
        $agenda = new self();
        $agenda->intervalos = $intervalos;
        $agenda->medicoUuid = $medicoUuid;
        
        return $agenda;
    }

    public function getIntervalos(): IntervalosCollection
    {
        return $this->intervalos;
    }

    public function getMedicoUuid(): UuidInterface
    {
        return $this->medicoUuid;
    }
}
