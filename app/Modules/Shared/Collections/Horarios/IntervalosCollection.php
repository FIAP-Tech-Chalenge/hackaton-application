<?php

namespace App\Modules\Shared\Collections\Horarios;

use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class IntervalosCollection implements IteratorAggregate, Countable
{
    private array $intervalos = [];

    public function add(IntervaloEntity $intervalo): self
    {
        $key = sprintf(
            '%s-%s',
            $intervalo->inicioDoIntervalo->format('H:i'),
            $intervalo->finalDoIntervalo->format('H:i')
        );
        $this->intervalos[$key] = $intervalo;
        return $this;
    }

    public function getIntervalos(): array
    {
        return $this->intervalos;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->intervalos);
    }

    public function count(): int
    {
        return count($this->intervalos);
    }
}
