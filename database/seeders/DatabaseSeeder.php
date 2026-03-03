<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        DB::table('tipo_usuarios')->insert([
            ['id' => 1, 'descricao' => 'gestor', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'descricao' => 'colaborador', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'descricao' => 'administrador do sistema', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('areas')->insert([
            [
                'id' => 1,
                'name' => 'Desenvolvimento',
                'slug' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 2. Populando a tabela de Usuários
        DB::table('usuarios')->insert([
            [
                'id' => 1,
                'nome' => 'igor',
                'email' => 'igor.schons',
                'senha' => \Illuminate\Support\Facades\Hash::make('123'),
                'codigo_tipo' => 2, // Colaborador
                'codigo_area' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nome' => 'Dalton',
                'email' => 'dalton@abase',
                'senha' => \Illuminate\Support\Facades\Hash::make('123'),
                'codigo_tipo' => 1, // Gestor
                'codigo_area' => 1, // Atrelado a Desenvolvimento
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
