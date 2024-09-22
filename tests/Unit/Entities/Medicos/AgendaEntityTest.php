<?php

namespace Tests\Unit\Entities\Medicos;

use App\Modules\Medicos\Entities\Horarios\AgendaEntity;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

#[Group('entities')]
#[Group('entities_agenda')]
class AgendaEntityTest extends TestCase
{
    public function test_deve_criar_uma_agenda(): void
    {
        $agenda = AgendaEntity::makeAgenda(
            new IntervalosCollection(),
            Uuid::uuid7()
        );
        $this->assertInstanceOf(AgendaEntity::class, $agenda);
        $this->assertInstanceOf(IntervalosCollection::class, $agenda->getIntervalos());
        $this->assertIsString($agenda->getMedicoUuid()->toString());
    }
}
