<?php

namespace Database\Factories;

use App\Models\HorarioDisponivel;
use App\Models\Paciente;
use App\Models\PacienteHorarioDisponivel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PacienteHorarioDisponivel>
 */
class PacienteHorarioDisponivelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'horario_disponivel_uuid' => HorarioDisponivel::factory(),
            'paciente_uuid' => Paciente::factory(),
            'assinatura_confirmacao' => null,
            'confirmado_em' => null,
        ];
    }
}
