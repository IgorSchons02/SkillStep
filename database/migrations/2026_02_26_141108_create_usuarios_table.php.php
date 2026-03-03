<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            // Chave primária padrão do Laravel (id)
            $table->id(); 
            
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('senha');            
            
            // Coluna de Tipo de Usuário (Gestor, Colaborador, Admin)
            $table->unsignedBigInteger('codigo_tipo')->default(2); // 2 = Colaborador por padrão
            
            // Relacionamento com a tabela de tipo_usuarios
            // Usamos 'restrict' para que o banco não deixe você apagar um "tipo" se já existirem usuários atrelados a ele
            $table->foreign('codigo_tipo')->references('id')->on('tipo_usuarios')->onDelete('restrict');
            
            // Coluna de Área (nullable porque o RH/Admin pode não ter área)
            $table->unsignedBigInteger('codigo_area')->nullable();
            
            // Relacionamento com a tabela de áreas
            // 'set null' garante que se uma área for apagada, o usuário não é deletado junto, apenas fica "sem área"
            $table->foreign('codigo_area')->references('id')->on('areas')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};