<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            // Define 'codigo' como a chave primária (Primary Key)
            $table->id('id'); 
            
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('senha');            
            // 'gestor 1' ou 'colaborador 2'
            $table->unsignedBigInteger('codigo_tipo')->default('2'); 
            
            // Relacionamento com a tabela de áreas
            $table->unsignedBigInteger('codigo_area')->nullable();
            $table->foreign('codigo_area')->references('id')->on('areas')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};