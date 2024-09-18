<?php

use App\Enums\StatusHorarioEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('horarios_disponiveis', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignUuid('medico_uuid')
                ->constrained('medicos', 'uuid')
                ->onDelete('cascade');
            $table->date('data');
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->smallInteger('status')
                ->default(StatusHorarioEnum::DISPONIVEL->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios_disponiveis');
    }
};
