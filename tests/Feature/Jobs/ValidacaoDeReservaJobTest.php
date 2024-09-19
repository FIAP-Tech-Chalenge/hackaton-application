<?php

namespace Tests\Feature\Jobs;

use App\Enums\StatusHorarioEnum;
use App\Jobs\Agendamento\ValidacaoDeReservaJob;
use App\Models\HorarioDisponivel;
use App\Models\Paciente;
use App\Models\PacienteHorarioDisponivel;
use App\Modules\Pacientes\Entities\ReservaEntity;
use App\Modules\Shared\Gateways\ReservarHorarioCommandInterface;
use App\Modules\Shared\Gateways\ReservarHorarioMapperInterface;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Uid\Ulid;
use Tests\TestCase;

#[Group('integration_job_validar_reserva_de_horario')]
class ValidacaoDeReservaJobTest extends TestCase
{
    use DatabaseMigrations;

    public function test_deve_confirmar_reserva()
    {
        // Arrange
        $paciente = Paciente::factory()->create();
        $horarioDisponivel = HorarioDisponivel::factory()->create();
        $reserva = PacienteHorarioDisponivel::factory()->create([
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $paciente->uuid,
            'assinatura_confirmacao' => Ulid::generate(),
        ]);

        // Act
        ValidacaoDeReservaJob::dispatch(
            new ReservaEntity(
                horarioDisponivelUuid: Uuid::fromString($horarioDisponivel->uuid),
                pacienteUuid: Uuid::fromString($paciente->uuid),
                assinaturaConfirmacao: $reserva->assinatura_confirmacao
            ),
            $this->app->make(ReservarHorarioCommandInterface::class),
            $this->app->make(ReservarHorarioMapperInterface::class)
        );

        // Assert
        $this->assertDatabaseHas('horarios_disponiveis', [
            'uuid' => $horarioDisponivel->uuid,
            'status' => StatusHorarioEnum::CONFIRMADO->value,
        ]);
        $this->assertDatabaseHas('paciente_horarios_disponiveis', [
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $paciente->uuid,
            'assinatura_confirmacao' => $reserva->assinatura_confirmacao,
        ]);
    }

}
