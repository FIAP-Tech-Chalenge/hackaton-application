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
        Schema::create('solicitacao_anonimizacoes', function (Blueprint $table) {
            $table->uuid()->toArray();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->dateTime('data_solicitacao');
            $table->dateTime('data_anonimizacao')->nullable();
            $table->integer('status')->default(0);
            $table->string('job_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitacao_anonimizacoes');
    }
};
