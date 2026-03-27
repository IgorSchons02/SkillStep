<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id(); 
            
            $table->string('titulo', 200);
            $table->text('descricao');
            
            /**
             * Relacionamento com a nova tabela de Categorias.
             * Usamos 'restrict' para não permitir deletar uma categoria que tenha tarefas.
             */
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('restrict');
            
            /**
             * Tempo estimado armazenado em MINUTOS (INT).
             * Onde 1.5h no front será 90 no banco.
             */
            $table->integer('tempo_estimado');
            
            /**
             * Status da tarefa (1 = Ativo, 0 = Inativo)
             */
            $table->boolean('status')->default(true);
            
            /**
             * Timestamps padrão e SoftDeletes para auditoria
             */
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarefas');
    }
};