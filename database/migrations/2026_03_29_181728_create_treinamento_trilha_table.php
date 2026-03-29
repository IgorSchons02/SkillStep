<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('treinamento_trilha', function (Blueprint $table) {
            $table->id();

            // Se excluir a trilha, remove a ligação
            $table->foreignId('trilha_id')->constrained('trilhas')->cascadeOnDelete();

            // Trava: Impede que um Treinamento seja excluído se estiver dentro de uma Trilha
            $table->foreignId('treinamento_id')->constrained('treinamentos')->restrictOnDelete();

            // Define a sequência dos treinamentos
            $table->integer('ordem');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treinamento_trilha');
    }
};