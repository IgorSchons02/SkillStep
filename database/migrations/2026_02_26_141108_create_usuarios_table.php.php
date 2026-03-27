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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id(); 
            
            $table->string('nome', 150);
            
            /**
             * CPF como identificador único. 
             * Usamos string para preservar zeros à esquerda e pontuação se necessário.
             */
            $table->string('cpf', 14)->unique(); 
            
            /**
             * O e-mail deixa de ser unique no banco para permitir que 
             * usuários deletados (Soft Delete) não bloqueiem novos cadastros,
             * a validação de e-mail ativo será feita via Controller.
             */
            $table->string('email', 191);
            
            $table->string('senha');            
            
            $table->enum('tipo_usuario', ['admin', 'supervisor', 'aluno'])->default('aluno');
            
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};