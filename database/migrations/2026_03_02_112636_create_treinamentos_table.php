<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treinamentos', function (Blueprint $table) {
            $table->id(); // Cria a coluna 'id' como Primary Key
            $table->string('nome');
            $table->text('descricao')->nullable(); // nullable permite salvar sem descrição
            $table->boolean('ativo')->default(true); // Sugestão: controle de visibilidade
            
            // Chave Estrangeira para a Área
            $table->unsignedBigInteger('codigo_area');
            $table->foreign('codigo_area')->references('id')->on('areas')->onDelete('cascade');
            
            $table->timestamps(); // Cria created_at e updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treinamentos');
    }
};
