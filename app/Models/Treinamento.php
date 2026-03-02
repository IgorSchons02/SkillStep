<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Treinamento extends Model
{
    use HasFactory;

    // Define o nome da tabela (útil caso o Laravel tente procurar por 'treinamentos' e se confunda)
    protected $table = 'treinamentos';

    // Campos que podem ser preenchidos em massa (Mass Assignment)
    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
        'codigo_area'
    ];

    /**
     * Relacionamento 1:N com a Área.
     * Um treinamento pertence a uma única área.
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'codigo_area');
    }

    /**
     * Relacionamento N:N com Tarefas.
     * Um treinamento possui várias tarefas através da tabela pivot.
     */
    public function tarefas()
    {
        return $this->belongsToMany(Tarefa::class, 'treinamento_tarefas', 'codigo_treinamento', 'codigo_tarefa')
                    ->withPivot('id', 'ordem') // Traz a coluna 'ordem' da tabela pivot junto com os dados
                    ->withTimestamps()         // Mantém os timestamps da tabela pivot atualizados
                    ->orderByPivot('ordem', 'asc'); // Já entrega as tarefas na ordem correta do passo a passo!
    }
}