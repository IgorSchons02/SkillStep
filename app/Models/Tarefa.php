<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarefa extends Model
{
    protected $table = 'tarefas';

    protected $fillable = [
        'categoria_id',
        'titulo',
        'descricao',
        'tempo_estimado',
        'status',
    ];

    /**
     * Uma tarefa pertence a uma categoria.
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function treinamentos()
    {
        // Retorna o relacionamento Many-to-Many usando a tabela pivot
        return $this->belongsToMany(Treinamento::class, 'tarefa_treinamento');
    }
}