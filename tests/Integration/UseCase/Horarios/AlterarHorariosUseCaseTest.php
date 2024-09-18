<?php

namespace Tests\Integration\UseCase\Horarios;

use App\Enums\IntervalosDeAgendamentosEnum;
use App\Enums\StatusHorarioEnum;
use App\Models\HorarioDisponivel;
use App\Models\Medico;
use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use App\Modules\Medicos\Entities\Horarios\PeriodoAtendimento;
use App\Modules\Medicos\UseCases\AlterarHorarios\AlterarHorariosUseCase;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use App\Modules\Shared\Exceptions\Horarios\ExistemConflitosDeHorariosException;
use App\Modules\Shared\Gateways\HorariosDisponiveisCommandInterface;
use App\Modules\Shared\Gateways\HorariosDisponiveisMapperInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

#[Group('integration_usecase_alterar_horarios')]
class AlterarHorariosUseCaseTest extends TestCase
{
    use DatabaseMigrations;

    private AlterarHorariosUseCase $useCase;

    public function test_deve_criar_um_novo_horario_sem_cancelar_outros_no_intervalor_disponivel()
    {
        // Arrange
        $dataDoAgendamento = now()->startOfDay();
        $intervalosIndisponiveis = new IntervalosCollection();
        $intervalosIndisponiveis->add(
            new IntervaloEntity(
                inicioDoIntervalo: Carbon::parse('12:00'),
                finalDoIntervalo: Carbon::parse('14:00'),
                statusHorarioEnum: StatusHorarioEnum::INDISPONIVEL
            )
        );

        $periodoAtendimento = new PeriodoAtendimento(Carbon::parse('08:00'), Carbon::parse('18:00'));

        $periodos = $periodoAtendimento->montarAgendaDoDia(
            intervalosDeAgendamentosEnum: IntervalosDeAgendamentosEnum::SESSENTA_MINUTOS,
            intervalosIndisponiveis: $intervalosIndisponiveis
        );

        $medico = Medico::factory()->create();
        foreach ($periodos as $periodo) {
            HorarioDisponivel::factory()->create([
                'uuid' => Uuid::uuid7()->toString(),
                'medico_uuid' => $medico->uuid,
                'data' => $dataDoAgendamento,
                'hora_inicio' => $periodo->inicioDoIntervalo->format('H:i'),
                'hora_fim' => $periodo->finalDoIntervalo->format('H:i'),
                'status' => $periodo->statusHorarioEnum->value,
            ]);
        }

        // Act
        $novoAgendamento = (new IntervaloEntity(
            inicioDoIntervalo: Carbon::parse('12:00'),
            finalDoIntervalo: Carbon::parse('14:00'),
            statusHorarioEnum: StatusHorarioEnum::DISPONIVEL
        ))->setUuid(Uuid::uuid7());

        $agendaEntity = $this->useCase->execute(
            horariosParaCancelarUuids: [],
            novoIntervalo: $novoAgendamento,
            medicoUuid: Uuid::fromString($medico->uuid),
            data: $dataDoAgendamento
        );

        // Assert
        $this->assertDatabaseCount('horarios_disponiveis', $periodos->count() + 1);
        $this->assertDatabaseHas('horarios_disponiveis', [
            'uuid' => $novoAgendamento->getUuid()->toString(),
            'medico_uuid' => $medico->uuid,
            'data' => $dataDoAgendamento->format('Y-m-d H:i:s'),
            'hora_inicio' => $novoAgendamento->inicioDoIntervalo->format('Y-m-d H:i:s'),
            'hora_fim' => $novoAgendamento->finalDoIntervalo->format('Y-m-d H:i:s'),
            'status' => $novoAgendamento->statusHorarioEnum->value,
        ]);

        /** @var IntervaloEntity $periodo */
        foreach ($periodos as $periodo) {
            $this->assertDatabaseHas('horarios_disponiveis', [
                'medico_uuid' => $medico->uuid,
                'data' => $dataDoAgendamento->format('Y-m-d H:i:s'),
                'hora_inicio' => $periodo->inicioDoIntervalo->format('H:i'),
                'hora_fim' => $periodo->finalDoIntervalo->format('H:i'),
                'status' => $periodo->statusHorarioEnum->value,
            ]);
        }
    }

    public function test_deve_alterar_horarios_quando_o_horario_min_e_max_informados_cancelar_horarios_solicitados()
    {
        // Arrange
        $dataDoAgendamento = now()->startOfDay();
        $intervalosIndisponiveis = new IntervalosCollection();
        $intervalosIndisponiveis->add(
            new IntervaloEntity(
                inicioDoIntervalo: Carbon::parse('12:00'),
                finalDoIntervalo: Carbon::parse('14:00'),
                statusHorarioEnum: StatusHorarioEnum::INDISPONIVEL
            )
        );

        $periodoAtendimento = new PeriodoAtendimento(Carbon::parse('08:00'), Carbon::parse('18:00'));

        $periodos = $periodoAtendimento->montarAgendaDoDia(
            intervalosDeAgendamentosEnum: IntervalosDeAgendamentosEnum::SESSENTA_MINUTOS,
            intervalosIndisponiveis: $intervalosIndisponiveis
        );

        // factory para o banco
        $horariosGerados = [];
        $medico = Medico::factory()->create();
        foreach ($periodos as $periodo) {
            $horario = HorarioDisponivel::factory()->create([
                'uuid' => Uuid::uuid7()->toString(),
                'medico_uuid' => $medico->uuid,
                'data' => $dataDoAgendamento,
                'hora_inicio' => $periodo->inicioDoIntervalo->format('H:i'),
                'hora_fim' => $periodo->finalDoIntervalo->format('H:i'),
                'status' => $periodo->statusHorarioEnum->value,
            ]);
            $horariosGerados[] = [
                'uuid' => $horario->uuid,
                'hora_inicio' => $horario->hora_inicio,
                'hora_fim' => $horario->hora_fim,
            ];
        }
        $horariosParaRemover = [
            $horariosGerados[0]['uuid'],
            $horariosGerados[1]['uuid'],
            $horariosGerados[2]['uuid'],
        ];
        $horariosGerados = array_slice($horariosGerados, 3);

        // Act
        $novoAgendamento = (new IntervaloEntity(
            inicioDoIntervalo: Carbon::parse('08:00'),
            finalDoIntervalo: Carbon::parse('10:00'),
            statusHorarioEnum: StatusHorarioEnum::DISPONIVEL
        ))->setUuid(Uuid::uuid7());

        $this->useCase->execute(
            horariosParaCancelarUuids: $horariosParaRemover,
            novoIntervalo: $novoAgendamento,
            medicoUuid: Uuid::fromString($medico->uuid),
            data: $dataDoAgendamento
        );

        // Assert
        foreach ($horariosGerados as $horario) {
            $this->assertDatabaseHas('horarios_disponiveis', [
                'uuid' => $horario['uuid'],
                'medico_uuid' => $medico->uuid,
                'data' => $dataDoAgendamento,
                'hora_inicio' => $horario['hora_inicio'],
                'hora_fim' => $horario['hora_fim'],
                'status' => StatusHorarioEnum::DISPONIVEL->value,
            ]);
        }
        foreach ($horariosParaRemover as $horario) {
            $this->assertDatabaseHas('horarios_disponiveis', [
                'uuid' => $horario,
                'status' => StatusHorarioEnum::INDISPONIVEL->value,
            ]);
        }
        $this->assertDatabaseHas('horarios_disponiveis', [
            'medico_uuid' => $medico->uuid,
            'data' => $dataDoAgendamento,
            'hora_inicio' => $novoAgendamento->inicioDoIntervalo,
            'hora_fim' => $novoAgendamento->finalDoIntervalo,
            'status' => StatusHorarioEnum::DISPONIVEL->value,
        ]);
    }

    public function test_nao_deve_alterar_horarios_quando_ocorrerem_conflitos()
    {
        // Arrange
        $dataDoAgendamento = now()->startOfDay();
        $intervalosIndisponiveis = new IntervalosCollection();
        $intervalosIndisponiveis->add(
            (new IntervaloEntity(
                inicioDoIntervalo: Carbon::parse('12:00'),
                finalDoIntervalo: Carbon::parse('14:00'),
                statusHorarioEnum: StatusHorarioEnum::INDISPONIVEL
            ))->setUuid(Uuid::uuid7())
        );

        $periodoAtendimento = new PeriodoAtendimento(Carbon::parse('08:00'), Carbon::parse('18:00'));

        $periodos = $periodoAtendimento->montarAgendaDoDia(
            intervalosDeAgendamentosEnum: IntervalosDeAgendamentosEnum::SESSENTA_MINUTOS,
            intervalosIndisponiveis: $intervalosIndisponiveis
        );

        // factory para o banco
        $horariosGerados = [];
        $medico = Medico::factory()->create();
        foreach ($periodos as $periodo) {
            $horario = HorarioDisponivel::factory()->create([
                'uuid' => Uuid::uuid7()->toString(),
                'medico_uuid' => $medico->uuid,
                'data' => $dataDoAgendamento,
                'hora_inicio' => $periodo->inicioDoIntervalo->format('H:i'),
                'hora_fim' => $periodo->finalDoIntervalo->format('H:i'),
                'status' => $periodo->statusHorarioEnum->value,
            ]);
            $horariosGerados[] = [
                'uuid' => $horario->uuid,
                'hora_inicio' => $horario->hora_inicio,
                'hora_fim' => $horario->hora_fim,
            ];
        }

        // Act
        $this->expectException(ExistemConflitosDeHorariosException::class);
        $novoAgendamento = new IntervaloEntity(
            inicioDoIntervalo: Carbon::parse('08:00'),
            finalDoIntervalo: Carbon::parse('10:00'),
            statusHorarioEnum: StatusHorarioEnum::DISPONIVEL
        );
        $this->useCase->execute(
            horariosParaCancelarUuids: [
                $horariosGerados[0]['uuid'],
                $horariosGerados[1]['uuid'],
            ],
            novoIntervalo: $novoAgendamento,
            medicoUuid: Uuid::fromString($medico->uuid),
            data: $dataDoAgendamento
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = new AlterarHorariosUseCase(
            horariosDisponiveisCommand: $this->app->make(HorariosDisponiveisCommandInterface::class),
            horariosDisponiveisMapper: $this->app->make(HorariosDisponiveisMapperInterface::class)
        );
    }
}
