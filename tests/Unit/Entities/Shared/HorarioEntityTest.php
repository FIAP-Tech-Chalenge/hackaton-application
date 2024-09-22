<?php

namespace Tests\Unit\Entities\Shared;

use App\Enums\StatusHorarioEnum;
use App\Modules\Shared\Entities\HorarioEntity;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class HorarioEntityTest extends TestCase
{
    public function test_deve_criar_um_horario_e_retornar_status_disponivel(): void
    {
        $horarioEntity = new HorarioEntity(
            Uuid::uuid7(),
            Uuid::uuid7(),
            Carbon::parse('08:00'),
            Carbon::parse('08:00'),
            Carbon::parse('08:00'),
            StatusHorarioEnum::DISPONIVEL
        );
        $horarioEntity->setStatus(StatusHorarioEnum::INDISPONIVEL);

        $this->assertEquals(StatusHorarioEnum::INDISPONIVEL, $horarioEntity->getStatus());
    }
}
