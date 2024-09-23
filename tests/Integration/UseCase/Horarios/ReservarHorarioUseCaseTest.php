<?php

namespace Tests\Integration\UseCase\Horarios;

use App\Enums\StatusHorarioEnum;
use App\Models\HorarioDisponivel;
use App\Models\Paciente;
use App\Modules\Pacientes\UseCases\ReservarHorarioUseCase;
use App\Modules\Shared\Exceptions\Horarios\ErroAoReservarHorarioException;
use App\Modules\Shared\Exceptions\Horarios\HorarioNaoDisponivelException;
use App\Modules\Shared\Exceptions\Horarios\HorarioNaoEncontradoException;
use App\Modules\Shared\Exceptions\Horarios\PacienteNaoEncontradoException;
use App\Modules\Shared\Gateways\Horarios\HorariosDisponiveisMapperInterface;
use App\Modules\Shared\Gateways\Pacientes\PacienteMapperInterface;
use App\Modules\Shared\Gateways\Reservas\ReservarHorarioCommandInterface;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

#[Group('integration_usecase_reservar_horario')]
class ReservarHorarioUseCaseTest extends TestCase
{
    use DatabaseMigrations;

    public static function statusDiferentesDeDisponivel(): array
    {
        return [
            'reservado' => [StatusHorarioEnum::RESERVADO],
            'indisponivel' => [StatusHorarioEnum::INDISPONIVEL],
            'confirmado' => [StatusHorarioEnum::CONFIRMADO],
            'cancelado' => [StatusHorarioEnum::CANCELADO],
        ];
    }

    public function test_deve_reservar_horario_com_sucesso_marcando_como_reservado_e_associando_paciente()
    {
        // Arrange
        $pacienteFactory = Paciente::factory()->create();
        $horarioDisponivel = HorarioDisponivel::factory()->create();

        // Act
        $reservaEntity = $this->useCase->execute(
            Uuid::fromString($horarioDisponivel->uuid),
            Uuid::fromString($pacienteFactory->uuid)
        );

        // Assert
        $this->assertDatabaseHas('paciente_horarios_disponiveis', [
            'horario_disponivel_uuid' => $horarioDisponivel->uuid,
            'paciente_uuid' => $pacienteFactory->uuid,
            'assinatura_confirmacao' => $reservaEntity->assinaturaConfirmacao
        ]);
        $this->assertDatabaseHas('horarios_disponiveis', [
            'uuid' => $horarioDisponivel->uuid,
            'status' => StatusHorarioEnum::RESERVADO->value
        ]);
    }

    #[DataProvider('statusDiferentesDeDisponivel')]
    public function test_nao_deve_permitir_reservar_horario_quando_horario_nao_estiver_disponivel(
        StatusHorarioEnum $statusHorarioEnum
    ) {
        // Arrange
        $pacienteFactory = Paciente::factory()->create();
        $horarioDisponivel = HorarioDisponivel::factory()->create([
            'status' => $statusHorarioEnum->value
        ]);

        // Act
        $this->expectException(HorarioNaoDisponivelException::class);
        $this->expectExceptionMessage('Horário não disponível');
        $this->useCase->execute(
            Uuid::fromString($horarioDisponivel->uuid),
            Uuid::fromString($pacienteFactory->uuid)
        );
    }

    public function test_nao_deve_permitir_reservar_horario_quando_horario_nao_existir()
    {
        // Arrange
        $pacienteFactory = Paciente::factory()->create();

        // Act
        $this->expectException(HorarioNaoEncontradoException::class);
        $this->expectExceptionMessage('Horário não encontrado');
        $this->useCase->execute(
            Uuid::uuid4(),
            Uuid::fromString($pacienteFactory->uuid)
        );
    }

    public function test_nao_deve_permitir_reservar_horario_quando_paciente_nao_existir()
    {
        // Arrange
        $horarioDisponivel = HorarioDisponivel::factory()->create();

        // Act
        $this->expectException(PacienteNaoEncontradoException::class);
        $this->expectExceptionMessage('Paciente não encontrad');
        $this->useCase->execute(
            Uuid::fromString($horarioDisponivel->uuid),
            Uuid::uuid4()
        );
    }

    public function test_nao_deve_permitir_reservar_horario_quando_horario_nao_for_reservado()
    {
        // Arrange
        $pacienteFactory = Paciente::factory()->create();
        $horarioDisponivel = HorarioDisponivel::factory()->create([
            'status' => StatusHorarioEnum::DISPONIVEL->value
        ]);
        $mockHorarioDisponivelCommand = $this->createMock(ReservarHorarioCommandInterface::class);
        $mockHorarioDisponivelCommand->method('reservarHorario')->willReturn(null);

        // Act
        $this->expectException(ErroAoReservarHorarioException::class);
        $this->expectExceptionMessage('Horário não disponível');
        $useCase = new ReservarHorarioUseCase(
            $this->app->make(HorariosDisponiveisMapperInterface::class),
            $mockHorarioDisponivelCommand,
            $this->app->make(PacienteMapperInterface::class)
        );
        $useCase->execute(
            Uuid::fromString($horarioDisponivel->uuid),
            Uuid::fromString($pacienteFactory->uuid)
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = new ReservarHorarioUseCase(
            $this->app->make(HorariosDisponiveisMapperInterface::class),
            $this->app->make(ReservarHorarioCommandInterface::class),
            $this->app->make(PacienteMapperInterface::class)
        );
    }
}
