<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Criando o Administrador do Sistema
        Usuario::create([
            'nome' => 'Admin SkillStep',
            'cpf' => '04106731900',
            'email' => 'admin@cigam.com.br',
            'senha' => Hash::make('123'),
            'tipo_usuario' => 'admin',
        ]);

        // 2. Criando um Supervisor (Antigo Gestor)
        Usuario::create([
            'nome' => 'Cristiano Supervisor',
            'cpf' => '12345678900',
            'email' => 'cristiano@cigam.com.br',
            'senha' => Hash::make('123'),
            'tipo_usuario' => 'supervisor',
        ]);

        // 3. Criando um Aluno (Você)
        Usuario::create([
            'nome' => 'Igor Schons',
            'cpf' => '98765432100',
            'email' => 'igor@cigam.com.br',
            'senha' => Hash::make('123'),
            'tipo_usuario' => 'aluno',
        ]);
        
        // Exemplo de criação em massa caso queira testar a listagem
        // Usuario::factory(10)->create(['tipo_usuario' => 'aluno']);
    }
}