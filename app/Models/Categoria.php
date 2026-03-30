<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = [
        'nome',
        'descricao',
        'cor_hex'
    ];

    /**
     * Uma categoria possui muitos treinamentos
     */
    public function tarefas()
    {
        return $this->hasMany(Tarefa::class, 'categoria_id');
    }
}