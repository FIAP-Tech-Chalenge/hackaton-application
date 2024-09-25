<?php

namespace Tests\Feature\Jobs;

use App\Enums\StatusHorarioEnum;
use App\Jobs\Agendamento\RestaurarHorariosNaoConfirmadosJob;
use App\Models\HorarioDisponivel;
use App\Models\Paciente;
use App\Models\PacienteHorarioDisponivel;
use App\Modules\Pacientes\Entities\ReservaEntity;
use App\Modules\Shared\Entities\HorarioReservadoEntity;
use App\Modules\Shared\Entities\MedicoEntity;
use App\Modules\Shared\Entities\PacienteEntity;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Uid\Ulid;
use Tests\TestCase;

#[Group('integration_job_validar_reserva_de_horario_nao_confirmado')]
class RestaurarHorariosNaoConfirmadosJobTest extends TestCase
{
    use DatabaseMigrations;

    public function test_deve_cancelar_reserva_de_horario_quando_nao_confirmado(): void
    {
        // Arrange
        Notification::fake();
        Queue::fake();
        $pacienteDaReserva = Paciente::factory()->create();
        $horarioDisponivel = HorarioDisponivel::factory()->create([
            'status' => StatusHorarioEnum::RESERVADO->value,
        ]);
        $reserva = PacienteHorarioDisponivel::factory()->create([
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $pacienteDaReserva->uuid,
            'assinatura_confirmacao' => Ulid::generate(),
        ]);
        $reservaEntity = new ReservaEntity(
            horarioDisponivelUuid: Uuid::fromString($horarioDisponivel->uuid),
            pacienteUuid: Uuid::fromString($pacienteDaReserva->uuid),
            assinaturaConfirmacao: $reserva->assinatura_confirmacao,
            medicoUuid: Uuid::fromString($horarioDisponivel->medico->uuid),
        );
        $horarioReservadoEntity = new HorarioReservadoEntity(
            horarioUuid: Uuid::fromString($horarioDisponivel->uuid),
            medicoEntity: new MedicoEntity(
                uuid: Uuid::fromString($horarioDisponivel->medico->uuid),
                nome: $horarioDisponivel->medico->nome,
                crm: $horarioDisponivel->medico->crm,
                email: $horarioDisponivel->medico->user->email,
            ),
            pacienteEntity: new PacienteEntity(
                uuid: Uuid::fromString($pacienteDaReserva->uuid),
                nome: $pacienteDaReserva->nome,
                cpf: $pacienteDaReserva->cpf,
                email: $pacienteDaReserva->user->email,
            ),
            data: $horarioDisponivel->data,
            horaInicio: $horarioDisponivel->hora_inicio,
            horaFim: $horarioDisponivel->hora_fim,
            status: StatusHorarioEnum::DISPONIVEL,
            assinaturaDoAgendamento: null
        );

        // Act
        (new RestaurarHorariosNaoConfirmadosJob(
            $reservaEntity,
            $horarioReservadoEntity
        ))->handle();

        // Assert
        $this->assertDatabaseHas('horarios_disponiveis', [
            'uuid' => $horarioDisponivel->uuid,
            'status' => StatusHorarioEnum::DISPONIVEL->value,
        ]);

        $this->assertDatabaseMissing('paciente_horarios_disponiveis', [
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $pacienteDaReserva->uuid,
            'assinatura_confirmacao' => $reserva->assinatura_confirmacao,
        ]);
    }
}
