<?php

namespace Tests\Integration\UseCase\Horarios;

use App\Enums\IntervalosDeAgendamentosEnum;
use App\Enums\StatusHorarioEnum;
use App\Helpers\BuilderHelper;
use App\Models\HorarioDisponivel;
use App\Models\Medico;
use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use App\Modules\Medicos\Entities\Horarios\PeriodoAtendimento;
use App\Modules\Medicos\UseCases\LiberarHorarios\LiberarHorariosUseCase;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use App\Modules\Shared\Exceptions\Horarios\MedicoJaPossuiAgendaNoDiaException;
use App\Modules\Shared\Gateways\Horarios\HorariosDisponiveisCommandInterface;
use App\Modules\Shared\Gateways\Horarios\HorariosDisponiveisMapperInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

#[Group('integration_usecase_liberar_horarios')]
class LiberarHorariosUseCaseTest extends TestCase
{
    use DatabaseMigrations;

    private LiberarHorariosUseCase $useCase;

    public function test_deve_gerar_horarios_disponiveis()
    {
        // Arrange
        $medico = Medico::factory()->create();

        $periodoAtendimento = new PeriodoAtendimento(Carbon::parse('08:00'), Carbon::parse('18:00'));
        $intervalosIndisponiveis = new IntervalosCollection();
        $intervalosIndisponiveis->add(
            intervalo: new IntervaloEntity(
                inicioDoIntervalo: Carbon::parse('12:00'),
                finalDoIntervalo: Carbon::parse('14:00'),
                statusHorarioEnum: StatusHorarioEnum::DISPONIVEL
            )
        );
        $dataDaAgenda = now()->startOfDay();

        // Act
        $agendaEntity = $this->useCase->execute(
            periodoAtendimento: $periodoAtendimento,
            intervalo: IntervalosDeAgendamentosEnum::SESSENTA_MINUTOS,
            intervalosIndisponiveis: $intervalosIndisponiveis,
            medicoUuid: Uuid::fromString($medico->uuid),
            data: $dataDaAgenda
        );

        // Assert
        $this->assertDatabaseCount('horarios_disponiveis', $agendaEntity->getIntervalos()->count());
        foreach ($agendaEntity->getIntervalos() as $intervalo) {
            $this->assertDatabaseHas('horarios_disponiveis', [
                'medico_uuid' => $medico->uuid,
                'data' => $dataDaAgenda,
                'hora_inicio' => $intervalo->inicioDoIntervalo,
                'hora_fim' => $intervalo->finalDoIntervalo,
                'status' => StatusHorarioEnum::DISPONIVEL->value
            ]);
        }

        foreach ($intervalosIndisponiveis as $indisponivel) {
            $horario = BuilderHelper::overlap(
                HorarioDisponivel::query(),
                'hora_inicio',
                'hora_fim',
                $indisponivel->inicioDoIntervalo,
                $indisponivel->finalDoIntervalo
            )
                ->where('medico_uuid', $medico->uuid)
                ->where('data', $dataDaAgenda);

            $this->assertFalse($horario->exists());
        }
    }

    public function test_nao_deve_permitir_criar_horarios_quando_medico_ja_possui_agenda_no_dia()
    {
        // Arrange
        $medico = Medico::factory()->create();
        $dataDaAgenda = now()->startOfDay();
        $intervalo = new IntervaloEntity(
            inicioDoIntervalo: Carbon::parse('08:00'),
            finalDoIntervalo: Carbon::parse('09:00'),
            statusHorarioEnum: StatusHorarioEnum::DISPONIVEL
        );
        HorarioDisponivel::factory()->create([
            'medico_uuid' => $medico->uuid,
            'data' => $dataDaAgenda,
            'hora_inicio' => $intervalo->inicioDoIntervalo,
            'hora_fim' => $intervalo->finalDoIntervalo,
            'status' => StatusHorarioEnum::DISPONIVEL->value
        ]);

        $periodoAtendimento = new PeriodoAtendimento(Carbon::parse('08:00'), Carbon::parse('18:00'));
        $intervalosIndisponiveis = new IntervalosCollection();
        $intervalosIndisponiveis->add($intervalo);

        // Act
        $this->expectException(MedicoJaPossuiAgendaNoDiaException::class);
        $this->expectExceptionMessage('Já existe uma agenda para o dia');
        $this->useCase->execute(
            periodoAtendimento: $periodoAtendimento,
            intervalo: IntervalosDeAgendamentosEnum::SESSENTA_MINUTOS,
            intervalosIndisponiveis: $intervalosIndisponiveis,
            medicoUuid: Uuid::fromString($medico->uuid),
            data: $dataDaAgenda
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = new LiberarHorariosUseCase(
            horariosDisponiveisCommand: $this->app->make(HorariosDisponiveisCommandInterface::class),
            horariosDisponiveisMapper: $this->app->make(HorariosDisponiveisMapperInterface::class)
        );
    }
}
