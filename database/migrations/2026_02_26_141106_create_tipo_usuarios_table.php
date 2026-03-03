<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_usuarios', function (Blueprint $table) {
            $table->id(); // Cria a coluna 'id' como Primary Key (Integer)
            $table->string('descricao'); // Cria a coluna 'descricao' (Varchar)
            
            $table->timestamps(); // Cria created_at e updated_at (Sempre recomendado no Laravel)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_usuarios');
    }
};
