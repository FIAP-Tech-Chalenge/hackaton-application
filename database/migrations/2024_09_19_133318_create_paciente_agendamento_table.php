<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paciente_agendamentos', function (Blueprint $table) {
            $table->foreignUuid('horario_disponivel_uuid')
                ->primary()
                ->constrained('horarios_disponiveis', 'uuid')
                ->onDelete('cascade');
            $table->foreignUuid('paciente_uuid')
                ->constrained('pacientes', 'uuid')
                ->onDelete('cascade');
            $table->dateTime('confirmado_em')
                ->nullable()
                ->default(null);
            $table->ulid('assinatura_confirmacao');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paciente_agendamentos');
    }
};
