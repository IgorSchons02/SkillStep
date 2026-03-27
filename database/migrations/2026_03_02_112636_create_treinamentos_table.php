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
        Schema::create('treinamentos', function (Blueprint $table) {
            $table->id(); 
            
            $table->string('nome', 200);
            $table->string('slug')->unique()->nullable(); // Para URLs amigáveis
            $table->text('descricao')->nullable();
            
            /**
             * Relacionamento com Categorias.
             * Importante: A migration de 'categorias' deve rodar antes desta.
             */
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('restrict');
            
            /**
             * Status do treinamento (Ativo/Inativo)
             */
            $table->boolean('status')->default(true);
            
            /**
             * Timestamps e SoftDeletes
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
        Schema::dropIfExists('treinamentos');
    }
};