<?php

namespace Tests\Feature\Jobs;

use App\Enums\StatusHorarioEnum;
use App\Jobs\Agendamento\ValidacaoDeReservaJob;
use App\Models\HorarioDisponivel;
use App\Models\Paciente;
use App\Models\PacienteHorarioDisponivel;
use App\Modules\Shared\Entities\HorarioReservadoEntity;
use App\Modules\Shared\Entities\MedicoEntity;
use App\Modules\Shared\Entities\PacienteEntity;
use App\Modules\Shared\Gateways\ReservarHorarioCommandInterface;
use App\Modules\Shared\Gateways\ReservarHorarioMapperInterface;
use App\Notifications\Agendamento\Reserva\ReservaConfirmadaMedicoMail;
use App\Notifications\Agendamento\Reserva\ReservaConfirmadaPacienteMail;
use App\Notifications\Agendamento\Reserva\ReservaReprovadaPacienteMail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Uid\Ulid;
use Tests\TestCase;

#[Group('integration_job_validar_reserva_de_horario')]
class ValidacaoDeReservaJobTest extends TestCase
{
    use DatabaseMigrations;

    public function test_deve_confirmar_reserva_e_enviar_email_quando_horario_estiver_reservado_para_paciente_e_medico()
    {
        // Arrange
        Notification::fake();

        $paciente = Paciente::factory()->create();
        $horarioDisponivel = HorarioDisponivel::factory()->create();
        $reserva = PacienteHorarioDisponivel::factory()->create([
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $paciente->uuid,
            'assinatura_confirmacao' => Ulid::generate(),
        ]);

        // Act
        ValidacaoDeReservaJob::dispatch(
            new HorarioReservadoEntity(
                horarioUuid: Uuid::fromString($horarioDisponivel->uuid),
                medicoEntity: new MedicoEntity(
                    uuid: Uuid::fromString($horarioDisponivel->medico->uuid),
                    nome: $horarioDisponivel->medico->nome,
                    crm: $horarioDisponivel->medico->crm,
                    email: $horarioDisponivel->medico->user->email
                ),
                pacienteEntity: new PacienteEntity(
                    uuid: Uuid::fromString($paciente->uuid),
                    nome: $paciente->nome,
                    cpf: $paciente->cpf,
                    email: $paciente->user->email
                ),
                data: $horarioDisponivel->data,
                horaInicio: $horarioDisponivel->hora_inicio,
                horaFim: $horarioDisponivel->hora_fim,
                status: StatusHorarioEnum::from($horarioDisponivel->status),
                assinaturaDoAgendamento: $reserva->assinatura_confirmacao
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
        Notification::assertSentTo([$horarioDisponivel->medico->user], ReservaConfirmadaMedicoMail::class);
        Notification::assertSentTo([$paciente->user], ReservaConfirmadaPacienteMail::class);
    }

    public function test_deve_enviar_email_quando_horario_nao_estiver_reservado()
    {
        // Arrange
        Notification::fake();
        $paciente = Paciente::factory()->create();
        $horarioDisponivel = HorarioDisponivel::factory()->create([
            'status' => StatusHorarioEnum::INDISPONIVEL->value,
        ]);
        $reserva = PacienteHorarioDisponivel::factory()->create([
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $paciente->uuid,
            'assinatura_confirmacao' => Ulid::generate(),
        ]);

        // Act
        ValidacaoDeReservaJob::dispatch(
            new HorarioReservadoEntity(
                horarioUuid: Uuid::fromString($horarioDisponivel->uuid),
                medicoEntity: new MedicoEntity(
                    uuid: Uuid::fromString($horarioDisponivel->medico->uuid),
                    nome: $horarioDisponivel->medico->nome,
                    crm: $horarioDisponivel->medico->crm,
                    email: $horarioDisponivel->medico->user->email
                ),
                pacienteEntity: new PacienteEntity(
                    uuid: Uuid::fromString($paciente->uuid),
                    nome: $paciente->nome,
                    cpf: $paciente->cpf,
                    email: $paciente->user->email
                ),
                data: $horarioDisponivel->data,
                horaInicio: $horarioDisponivel->hora_inicio,
                horaFim: $horarioDisponivel->hora_fim,
                status: StatusHorarioEnum::from($horarioDisponivel->status),
                assinaturaDoAgendamento: $reserva->assinatura_confirmacao
            ),
            $this->app->make(ReservarHorarioCommandInterface::class),
            $this->app->make(ReservarHorarioMapperInterface::class)
        );

        // Assert
        Notification::assertSentTo([$paciente->user], ReservaReprovadaPacienteMail::class);
    }

}
