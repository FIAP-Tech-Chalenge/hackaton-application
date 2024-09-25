<?php

namespace Tests\Integration\E2e\Pacientes\ListaDeHorarios;

use App\Enums\IntervalosDeAgendamentosEnum;
use App\Enums\StatusHorarioEnum;
use App\Enums\TipoUsuarioEnum;
use App\Models\HorarioDisponivel;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use App\Modules\Medicos\Entities\Horarios\IntervaloEntity;
use App\Modules\Medicos\Entities\Horarios\PeriodoAtendimento;
use App\Modules\Shared\Collections\Horarios\IntervalosCollection;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

#[Group('integration_e2e_lista_de_horarios')]
class ListaDeHorariosDisponiveisTest extends TestCase
{
    use DatabaseMigrations;

    public function test_listar_horarios_disponiveis(): void
    {
        // Arrange
        $medicoFactory = Medico::factory()->create();
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
        foreach ($periodos as $periodo) {
            HorarioDisponivel::factory()->create([
                'uuid' => Uuid::uuid7()->toString(),
                'medico_uuid' => $medicoFactory->uuid,
                'data' => $dataDoAgendamento,
                'hora_inicio' => $periodo->inicioDoIntervalo->format('H:i'),
                'hora_fim' => $periodo->finalDoIntervalo->format('H:i'),
                'status' => $periodo->statusHorarioEnum->value,
            ]);
        }
        $user = User::factory()->create([
            'tipo' => TipoUsuarioEnum::PACIENTE->value,
        ]);
        $paccienteFactory = Paciente::factory()->create([
            'user_id' => $user->id,
        ]);
        Sanctum::actingAs($user, ['paciente']);

        // Act
        $response = $this->getJson(route('pacientes.horarios.disponiveis', [
            'medicoUuid' => $medicoFactory->uuid,
            'data' => $dataDoAgendamento->format('Y-m-d'),
        ]));

        // Assert
        $response->assertStatus(200);
        $responseData = $response->json();

        $this->assertIsArray($responseData);
        foreach ($responseData['horarios'] as $horario) {
            $this->assertArrayHasKey('horario_uuid', $horario);
            $this->assertArrayHasKey('hora_inicio', $horario);
            $this->assertArrayHasKey('hora_fim', $horario);
            $this->assertArrayHasKey('status', $horario);
            $this->assertArrayHasKey('label', $horario['status']);
            $this->assertArrayHasKey('value', $horario['status']);
        }
    }

    public function test_deve_estar_unauthorized_quando_um_medico_tenta_listar_o_endpoint_paciente(): void
    {
        // Arrange
        $medicoFactory = Medico::factory()->create();
        $dataDoAgendamento = now()->startOfDay();

        // Act
        $response = $this->getJson(route('pacientes.horarios.disponiveis', [
            'medicoUuid' => $medicoFactory->uuid,
            'data' => $dataDoAgendamento->format('Y-m-d'),
        ]));

        // Assert
        $response->assertStatus(401);
    }
}
