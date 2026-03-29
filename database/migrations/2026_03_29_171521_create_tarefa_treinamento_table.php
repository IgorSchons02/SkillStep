<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tarefa_treinamento', function (Blueprint $table) {
            $table->id();

            // Se excluir o treinamento, remove a ligação
            $table->foreignId('treinamento_id')->constrained('treinamentos')->cascadeOnDelete();

            // restrictOnDelete: É a nossa trava de banco de dados. 
            // Impede que alguém exclua uma Tarefa se ela já estiver dentro de um Treinamento.
            $table->foreignId('tarefa_id')->constrained('tarefas')->restrictOnDelete();

            // Coluna de ouro: garante a sequência exata em que o usuário arrastou/clicou
            $table->integer('ordem');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarefa_treinamento');
    }
};