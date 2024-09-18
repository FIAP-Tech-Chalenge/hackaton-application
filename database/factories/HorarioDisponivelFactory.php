<?php

namespace Database\Factories;

use App\Enums\StatusHorarioEnum;
use App\Models\HorarioDisponivel;
use App\Models\Medico;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HorarioDisponivel>
 */
class HorarioDisponivelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $now = now()->addDay();
        return [
            'uuid' => $this->faker->uuid,
            'medico_uuid' => Medico::factory(),
            'data' => $now,
            'hora_inicio' => $now->addHour(),
            'hora_fim' => $now->addHour(),
            'status' => StatusHorarioEnum::DISPONIVEL->value,
        ];
    }
}
