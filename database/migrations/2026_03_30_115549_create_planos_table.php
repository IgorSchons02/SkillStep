<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planos', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com o Aluno (tabela usuarios)
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            
            // Adicionado ->unique()
            $table->string('titulo', 150)->unique();
            
            // Cache do progresso para a tela de listagem carregar rápido (0 a 100)
            $table->tinyInteger('progresso')->default(0); 
            
            // Coluna JSON que vai guardar toda a árvore de Trilhas, Treinos e Tarefas
            // com suas ordens customizadas, datas sugeridas e status de 'concluido'
            $table->json('estrutura'); 
            $table->json('supervisores_ids')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planos');
    }
};