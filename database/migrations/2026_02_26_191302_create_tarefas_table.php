<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com a Categoria
            $table->foreignId('categoria_id')
                  ->constrained('categorias')
                  ->restrictOnDelete(); // Impede excluir a categoria se tiver tarefas nela
            
            // Campo de Título agora com a restrição Unique Key (UK)
            $table->string('titulo', 100)->unique();
            
            // Usamos string longa ou text para garantir que URLs gigantes caibam sem cortar
            $table->text('descricao'); 
            
            // Decimal com 1 casa após a vírgula (Ex: 0.5, 1.5, 10.0)
            $table->decimal('tempo_estimado', 5, 1); 
            
            // Status para inativar (já que não teremos soft delete)
            $table->boolean('status')->default(true);
            
            // Gera as colunas 'created_at' e 'updated_at'
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarefas');
    }
};