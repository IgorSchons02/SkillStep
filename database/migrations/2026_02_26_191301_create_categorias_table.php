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
        Schema::create('categorias', function (Blueprint $table) {
            $table->id(); 
            
            // Nome da categoria (Ex: Back-end, Soft Skills, Onboarding)
            $table->string('nome', 100)->unique();
            
            // Uma breve descrição do que essa categoria abrange
            $table->string('descricao', 255)->nullable();
            
            // Cor para identificação visual no Front-end (Ex: #ff5733)
            $table->string('cor_hex', 7)->default('#3498db');

            $table->timestamps();
            $table->softDeletes(); // Mantendo o padrão de segurança de dados
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};