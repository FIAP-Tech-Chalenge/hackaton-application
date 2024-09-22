<?php

namespace Tests\Integration\E2e\Medicos\Horarios;

use App\Enums\IntervalosDeAgendamentosEnum;
use App\Enums\StatusHorarioEnum;
use App\Enums\TipoUsuarioEnum;
use App\Models\HorarioDisponivel;
use App\Models\Medico;
use App\Models\User;
use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use App\Modules\Medicos\Entities\Horarios\PeriodoAtendimento;
use App\Modules\Medicos\UseCases\AlterarHorarios\AlterarHorariosUseCase;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use App\Modules\Shared\Gateways\HorariosDisponiveisCommandInterface;
use App\Modules\Shared\Gateways\HorariosDisponiveisMapperInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

#[Group('integration_e2e_alterar_horarios')]
class AlterarHorariosE2eTest extends TestCase
{
    use DatabaseMigrations;

    private AlterarHorariosUseCase $useCase;

    public function test_deve_criar_um_novo_horario_sem_cancelar_outros_no_intervalor_disponivel()
    {
        // Arrange
        $user = User::factory()->create([
            'tipo' => TipoUsuarioEnum::MEDICO->value,
        ]);
        $this->actingAs($user,);

        Medico::factory()->create([
            'user_uuid' => $user->uuid,
        ]);

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
                'hora_inicio' => $periodo->inicioDoIntervalo->format('Y-m-d H:i'),
                'hora_fim' => $periodo->finalDoIntervalo->format('Y-m-d H:i'),
                'status' => $periodo->statusHorarioEnum->value,
            ]);
        }

        // Act
        $response = $this->postJson(
            route('medicos.horarios.alterar'),
            [
                'data' => $dataDoAgendamento->format('Y-m-d'),
                'novo_intervalo' => [
                    'hora_inicio' => $dataDoAgendamento->format('Y-m-d') . ' 12:00',
                    'hora_fim' => $dataDoAgendamento->format('Y-m-d') . ' 14:00',
                ],
                'horarios_para_cancelar' => [],
            ],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        );

        // Assert
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'count_horarios',
            'horarios_cancelados'
        ]);
        $response->assertJsonCount(0, 'horarios_cancelados');
    }

    public function test_deve_alterar_horarios_quando_o_horario_min_e_max_informados_cancelar_horarios_solicitados()
    {
        // Arrange
        $user = User::factory()->create([
            'tipo' => TipoUsuarioEnum::MEDICO->value,
        ]);
        $this->actingAs($user,);

        $dataDoAgendamento = now()->startOfDay();
        $intervalosIndisponiveis = new IntervalosCollection();
        $intervalosIndisponiveis->add(
            new IntervaloEntity(
                inicioDoIntervalo: Carbon::parse('12:00'),
                finalDoIntervalo: Carbon::parse('14:00'),
                statusHorarioEnum: StatusHorarioEnum::INDISPONIVEL
            )
        );

        $medico = Medico::factory()->create([
            'user_uuid' => $user->uuid,
        ]);
        $periodoAtendimento = new PeriodoAtendimento(Carbon::parse('08:00'), Carbon::parse('18:00'));
        $periodos = $periodoAtendimento->montarAgendaDoDia(
            intervalosDeAgendamentosEnum: IntervalosDeAgendamentosEnum::SESSENTA_MINUTOS,
            intervalosIndisponiveis: $intervalosIndisponiveis
        );
        foreach ($periodos as $periodo) {
            $horario = HorarioDisponivel::factory()->create([
                'uuid' => Uuid::uuid7()->toString(),
                'medico_uuid' => $medico->uuid,
                'data' => $dataDoAgendamento,
                'hora_inicio' => $periodo->inicioDoIntervalo->format('Y-m-d H:i'),
                'hora_fim' => $periodo->finalDoIntervalo->format('Y-m-d H:i'),
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
        $response = $this->postJson(
            route('medicos.horarios.alterar'),
            [
                'data' => $dataDoAgendamento->format('Y-m-d'),
                'novo_intervalo' => [
                    'hora_inicio' => $dataDoAgendamento->format('Y-m-d') . ' 12:00',
                    'hora_fim' => $dataDoAgendamento->format('Y-m-d') . ' 14:00',
                ],
                'horarios_para_cancelar' => $horariosParaRemover,
            ],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        );

        // Assert
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'count_horarios',
            'horarios_cancelados'
        ]);
        $response->assertJsonCount(count($horariosGerados), 'horarios_cancelados');
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
