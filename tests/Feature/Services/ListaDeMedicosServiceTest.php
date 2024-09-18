<?php

namespace Tests\Feature\Services;

use App\Http\Services\Pacientes\ListaDeMedicosService;
use App\Models\Medico;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('integration_service_pacientes_listar_medicos')]
class ListaDeMedicosServiceTest extends TestCase
{
    use DatabaseMigrations;

    public function test_deve_retornar_a_lista_de_medicos_cadastrados_no_sistema(): void
    {
        // Arrange
        Medico::factory()->count(5)->create();

        // Act
        $service = new ListaDeMedicosService();
        $medicos = $service->execute();
        $medicos = $medicos->toArray();

        // Assert
        $this->assertCount(5, $medicos['data']);
        $this->assertArrayHasKey('id', $medicos['data'][0]);
        $this->assertArrayHasKey('nome', $medicos['data'][0]);
        $this->assertArrayHasKey('crm', $medicos['data'][0]);
        $this->assertArrayHasKey('cpf', $medicos['data'][0]);
    }
}
