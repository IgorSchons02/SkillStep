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
            // Define 'id' como chave primária autoincremento (seu novo padrão)
            $table->id(); 
            
            $table->string('titulo');
            $table->text('descricao')->nullable(); // Text permite descrições longas
            
            // Relacionamento com a tabela de áreas
            $table->unsignedBigInteger('codigo_area');
            $table->foreign('codigo_area')->references('id')->on('areas')->onDelete('cascade');
            
            // O Laravel já gerencia datas de criação com o timestamps()
            // Mas incluímos conforme sua solicitação de 'data_criacao'
            $table->date('data_criacao')->useCurrent(); 
            
            $table->timestamps();
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