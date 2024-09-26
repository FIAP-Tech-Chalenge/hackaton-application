<?php

namespace Tests\Feature\Jobs;

use App\Enums\TipoUsuarioEnum;
use App\Jobs\LGPD\SolicitarAnonimizacaoScheduleJob;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('integration_job_solicitar_anonimizacao')]
class SolicitarAnonimizacaoScheduleJobTest extends TestCase
{
    use DatabaseMigrations;

    public function test_deve_anonimizar_o_usuario_medico()
    {
        // Arrange
        $user = User::factory()->create([
            'tipo' => TipoUsuarioEnum::MEDICO->value,
        ]);
        Medico::factory()->create([
            'user_id' => $user->id,
        ]);

        // Act
        $job = new SolicitarAnonimizacaoScheduleJob($user);
        $job->handle();

        // Assert
        $this->assertDatabaseHas('users', [
            'uuid' => $user->uuid,
            'email' => SolicitarAnonimizacaoScheduleJob::$maskEmail,
        ]);
        $this->assertDatabaseHas('medicos', [
            'user_id' => $user->id,
            'cpf' => SolicitarAnonimizacaoScheduleJob::$maskCpf,
            'crm' => SolicitarAnonimizacaoScheduleJob::$maskCrm,
        ]);
    }

    public function test_deve_anonimizar_o_usuario_paciente()
    {
        // Arrange
        $user = User::factory()->create([
            'tipo' => TipoUsuarioEnum::PACIENTE->value,
        ]);
        Paciente::factory()->create([
            'user_id' => $user->id,
        ]);

        // Act
        $job = new SolicitarAnonimizacaoScheduleJob($user);
        $job->handle();

        // Assert
        $this->assertDatabaseHas('users', [
            'uuid' => $user->uuid,
            'email' => SolicitarAnonimizacaoScheduleJob::$maskEmail,
        ]);
        $this->assertDatabaseHas('pacientes', [
            'user_id' => $user->id,
            'cpf' => SolicitarAnonimizacaoScheduleJob::$maskCpf,
        ]);
    }
}
