<?php

namespace Tests\Feature\Jobs;

use App\Enums\StatusHorarioEnum;
use App\Jobs\Agendamento\ValidacaoDeReservaJob;
use App\Models\HorarioDisponivel;
use App\Models\Paciente;
use App\Models\PacienteAgendamento;
use App\Modules\Pacientes\Entities\ReservaEntity;
use App\Modules\Shared\Gateways\Reservas\ReservarHorarioCommandInterface;
use App\Modules\Shared\Gateways\Reservas\ReservarHorarioMapperInterface;
use App\Notifications\Agendamento\Reserva\ReservaConfirmadaMedicoMail;
use App\Notifications\Agendamento\Reserva\ReservaConfirmadaPacienteMail;
use App\Notifications\Agendamento\Reserva\ReservaReprovadaPacienteMail;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
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
        $horarioDisponivel = HorarioDisponivel::factory()->create([
            'status' => StatusHorarioEnum::RESERVADO->value,
        ]);
        $reserva = PacienteAgendamento::factory()->create([
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $paciente->uuid,
            'assinatura_confirmacao' => Ulid::generate(),
        ]);

        // Act
        ValidacaoDeReservaJob::dispatch(
            new ReservaEntity(
                horarioDisponivelUuid: Uuid::fromString($horarioDisponivel->uuid),
                pacienteUuid: Uuid::fromString($paciente->uuid),
                assinaturaConfirmacao: $reserva->assinatura_confirmacao,
                medicoUuid: Uuid::fromString($horarioDisponivel->medico->uuid),
            ),
            $this->app->make(ReservarHorarioCommandInterface::class),
            $this->app->make(ReservarHorarioMapperInterface::class)
        );

        // Assert
        $this->assertDatabaseHas('horarios_disponiveis', [
            'uuid' => $horarioDisponivel->uuid,
            'status' => StatusHorarioEnum::CONFIRMADO->value,
        ]);
        $this->assertDatabaseHas('paciente_agendamentos', [
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $paciente->uuid,
            'assinatura_confirmacao' => $reserva->assinatura_confirmacao,
        ]);
        Notification::assertSentTo([$horarioDisponivel->medico->user], ReservaConfirmadaMedicoMail::class);
        Notification::assertSentTo([$paciente->user], ReservaConfirmadaPacienteMail::class);
    }

    public function test_deve_enviar_email_de_cancelamento_quando_horario_nao_estiver_reservado()
    {
        // Arrange
        Notification::fake();
        $paciente = Paciente::factory()->create();
        $horarioDisponivel = HorarioDisponivel::factory()->create([
            'status' => StatusHorarioEnum::INDISPONIVEL->value,
        ]);
        $reserva = PacienteAgendamento::factory()->create([
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $paciente->uuid,
            'assinatura_confirmacao' => Ulid::generate(),
        ]);

        // Act
        ValidacaoDeReservaJob::dispatch(
            new ReservaEntity(
                horarioDisponivelUuid: Uuid::fromString($horarioDisponivel->uuid),
                pacienteUuid: Uuid::fromString($paciente->uuid),
                assinaturaConfirmacao: $reserva->assinatura_confirmacao,
                medicoUuid: Uuid::fromString($horarioDisponivel->medico->uuid),
            ),
            $this->app->make(ReservarHorarioCommandInterface::class),
            $this->app->make(ReservarHorarioMapperInterface::class)
        );

        // Assert
        Notification::assertSentTo([$paciente->user], ReservaReprovadaPacienteMail::class);
    }

    public function test_deve_enviar_email_quando_horario_nao_o_paciente_nao_for_o_mesmo_que_fez_a_reserva()
    {
        // Arrange
        Notification::fake();
        $pacienteSolicitante = Paciente::factory()->create();
        $pacienteDaReserva = Paciente::factory()->create();
        $horarioDisponivel = HorarioDisponivel::factory()->create([
            'status' => StatusHorarioEnum::RESERVADO->value,
        ]);
        $reserva = PacienteAgendamento::factory()->create([
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $pacienteDaReserva->uuid,
            'assinatura_confirmacao' => Ulid::generate(),
        ]);

        // Act
        ValidacaoDeReservaJob::dispatch(
            new ReservaEntity(
                horarioDisponivelUuid: Uuid::fromString($horarioDisponivel->uuid),
                pacienteUuid: Uuid::fromString($pacienteSolicitante->uuid),
                assinaturaConfirmacao: $reserva->assinatura_confirmacao,
                medicoUuid: Uuid::fromString($horarioDisponivel->medico->uuid),
            ),
            $this->app->make(ReservarHorarioCommandInterface::class),
            $this->app->make(ReservarHorarioMapperInterface::class)
        );

        // Assert
        Notification::assertSentTo([$pacienteSolicitante->user], ReservaReprovadaPacienteMail::class);
    }

    public function test_nao_deve_realizar_nenhum_acao_quando_horario_reservado_nao_for_encontrado()
    {
        // Arrange
        Notification::fake();
        $paciente = Paciente::factory()->create();
        $horarioDisponivel = HorarioDisponivel::factory()->create([
            'status' => StatusHorarioEnum::RESERVADO->value,
        ]);
        $reserva = PacienteAgendamento::factory()->create([
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $paciente->uuid,
            'assinatura_confirmacao' => Ulid::generate(),
        ]);

        // Act
        ValidacaoDeReservaJob::dispatch(
            new ReservaEntity(
                horarioDisponivelUuid: Uuid::uuid7(),
                pacienteUuid: Uuid::fromString($paciente->uuid),
                assinaturaConfirmacao: $reserva->assinatura_confirmacao,
                medicoUuid: Uuid::fromString($horarioDisponivel->medico->uuid),
            ),
            $this->app->make(ReservarHorarioCommandInterface::class),
            $this->app->make(ReservarHorarioMapperInterface::class)
        );

        // Assert
        Notification::assertNothingSent();
    }

    #[Group('integration_job_validar_reserva_de_horario_rollback')]
    public function test_deve_realizar_rollback_quando_ocorrer_erro()
    {
        // Arrange
        Notification::fake();
        Queue::fake();

        $paciente = Paciente::factory()->create();
        $horarioDisponivel = HorarioDisponivel::factory()->create([
            'status' => StatusHorarioEnum::RESERVADO->value,
        ]);
        $reserva = PacienteAgendamento::factory()->create([
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $paciente->uuid,
            'assinatura_confirmacao' => Ulid::generate(),
        ]);

        $reservarHorarioCommand = $this->createMock(ReservarHorarioCommandInterface::class);
        $reservarHorarioCommand->method('confirmarReserva')
            ->willThrowException(new Exception('Erro ao reservar horário'));
        $reservarHorarioMapper = $this->app->make(ReservarHorarioMapperInterface::class);

        // Act
        $validacaoJob = new ValidacaoDeReservaJob(
            new ReservaEntity(
                horarioDisponivelUuid: Uuid::fromString($horarioDisponivel->uuid),
                pacienteUuid: Uuid::fromString($paciente->uuid),
                assinaturaConfirmacao: $reserva->assinatura_confirmacao,
                medicoUuid: Uuid::fromString($horarioDisponivel->medico->uuid),
            ),
            $reservarHorarioCommand,
            $reservarHorarioMapper
        );
        $validacaoJob->handle();

        // Assert
        $this->assertDatabaseHas('horarios_disponiveis', [
            'uuid' => $horarioDisponivel->uuid,
            'status' => StatusHorarioEnum::RESERVADO->value,
        ]);
        $this->assertDatabaseHas('paciente_agendamentos', [
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $paciente->uuid,
            'assinatura_confirmacao' => $reserva->assinatura_confirmacao,
        ]);
    }
}
