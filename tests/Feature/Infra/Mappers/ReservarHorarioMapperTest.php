<?php

namespace Tests\Feature\Infra\Mappers;

use App\Enums\StatusHorarioEnum;
use App\Models\HorarioDisponivel;
use App\Modules\Shared\Gateways\Reservas\ReservarHorarioMapperInterface;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class ReservarHorarioMapperTest extends TestCase
{
    use DatabaseMigrations;

    public function test_deve_retornar_null_quando_nao_houver_horario_reservado(): void
    {
        $reservarHorarioMapper = $this->app->make(ReservarHorarioMapperInterface::class);
        $retorno = $reservarHorarioMapper->getDetalhesDaReserva(Uuid::uuid7());
        $this->assertNull($retorno);
    }

    public function test_deve_retornar_null_quando_houver_horario_disponivel_(): void
    {
        $horarioFactory = HorarioDisponivel::factory()->create([
            'status' => StatusHorarioEnum::RESERVADO->value
        ]);

        $reservarHorarioMapper = $this->app->make(ReservarHorarioMapperInterface::class);

        $retorno = $reservarHorarioMapper->getDetalhesDaReserva(Uuid::fromString($horarioFactory->uuid));

        $this->assertNull($retorno);
    }
}
