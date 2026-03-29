<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Treinamento extends Model
{
    protected $table = 'treinamentos';

    protected $fillable = [
        'nome',
        'descricao',
        'status',
    ];

    /**
     * Relacionamento Muito-Para-Muitos com Tarefas.
     * O withPivot puxa a coluna 'ordem'.
     * O orderBy garante que as tarefas sempre venham na sequência certa da jornada.
     */
    public function tarefas()
    {
        return $this->belongsToMany(Tarefa::class, 'tarefa_treinamento')
            ->withPivot('ordem')
            ->orderBy('tarefa_treinamento.ordem', 'asc');
    }

    public function trilhas()
    {
        return $this->belongsToMany(Trilha::class, 'treinamento_trilha');
    }
}