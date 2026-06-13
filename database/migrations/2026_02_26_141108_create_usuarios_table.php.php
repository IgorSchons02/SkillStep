<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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
             */
            $table->string('cpf', 11)->unique();

            $table->string('email', 191);

            $table->string('senha');

            $table->enum('tipo_usuario', ['admin', 'supervisor', 'aluno'])->default('aluno');
            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};