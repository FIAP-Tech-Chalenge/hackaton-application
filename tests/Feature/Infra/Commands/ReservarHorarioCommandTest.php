<?php

namespace Tests\Feature\Infra\Commands;

use App\Enums\StatusHorarioEnum;
use App\Modules\Shared\Entities\HorarioEntity;
use App\Modules\Shared\Gateways\Reservas\ReservarHorarioCommandInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class ReservarHorarioCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_deve_retornar_null_quando_nao_houver_horario_reservado(): void
    {
        $reservarHorarioCommand = $this->app->make(ReservarHorarioCommandInterface::class);
        $retorno = $reservarHorarioCommand->reservarHorario(
            horarioEntity: new HorarioEntity(
                horarioUuid: Uuid::uuid7(),
                medicoUuid: Uuid::uuid7(),
                data: now(),
                horaInicio: now()->subMinutes(30),
                horaFim: now(),
                status: StatusHorarioEnum::DISPONIVEL
            ),
            pacienteUuid: Uuid::uuid7()
        );
        $this->assertNull($retorno);
    }
}
