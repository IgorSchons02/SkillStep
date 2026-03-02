<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treinamento_tarefas', function (Blueprint $table) {
            $table->id(); // PK da tabela pivot (útil para ordenação/edição pontual)
            
            $table->unsignedBigInteger('codigo_treinamento');
            $table->unsignedBigInteger('codigo_tarefa');
            $table->integer('ordem')->default(0); // Para garantir a sequência do treinamento
            
            // Chaves Estrangeiras com Cascade Delete
            $table->foreign('codigo_treinamento')
                  ->references('id')->on('treinamentos')
                  ->onDelete('cascade'); // Se apagar o treino, apaga este vínculo
                  
            $table->foreign('codigo_tarefa')
                  ->references('id')->on('tarefas')
                  ->onDelete('cascade'); // Se apagar a tarefa original, remove do treinamento
                  
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treinamento_tarefas');
    }
};
