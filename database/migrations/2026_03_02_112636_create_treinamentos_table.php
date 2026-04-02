<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('treinamentos', function (Blueprint $table) {
            $table->id();
            
            // Adicionado ->unique()
            $table->string('nome', 100)->unique(); 
            
            $table->string('descricao', 255)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treinamentos');
    }
};