<?php

namespace Tests\Unit\Collections;

use App\Enums\StatusHorarioEnum;
use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class IntervalosCollectionTest extends TestCase
{
    public function test_deve_retornar_o_primeiro_intervalo(): void
    {
        $intervaloCollection = new IntervalosCollection();
        $intervaloCollection->add(
            new IntervaloEntity(
                Carbon::parse('08:00'),
                Carbon::parse('09:00'),
                StatusHorarioEnum::DISPONIVEL
            )
        );

        $this->assertInstanceOf(IntervaloEntity::class, $intervaloCollection->first());
    }

    public function test_deve_retornar_null_quando_nao_houver_intervalos(): void
    {
        $intervaloCollection = new IntervalosCollection();
        $this->assertNull($intervaloCollection->first());
    }
}
