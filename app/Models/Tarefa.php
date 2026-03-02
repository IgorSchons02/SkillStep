<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarefa extends Model
{
    // Define o nome exato da tabela no banco de dados
    protected $table = 'tarefas';

    // Campos que podem ser preenchidos via formulário (Mass Assignment)
    protected $fillable = [
        'titulo',
        'descricao',
        'codigo_area',
        'data_criacao'
    ];

    // O Laravel gerencia o created_at/updated_at. Se você usar apenas data_criacao,
    // pode desativar os timestamps ou mantê-los para auditoria.
    public $timestamps = true;

    /**
     * Relacionamento: Uma tarefa pertence a uma área específica.
     */
    public function area(): BelongsTo
    {
        // Relaciona a tarefa com a área através da FK 'codigo_area'
        return $this->belongsTo(Area::class, 'codigo_area', 'id');
    }
    /**
     * Relacionamento N:N com Treinamentos.
     * Uma tarefa pode estar presente em vários treinamentos.
     */
    public function treinamentos()
    {
        return $this->belongsToMany(Treinamento::class, 'treinamento_tarefas', 'codigo_tarefa', 'codigo_treinamento')
                    ->withPivot('id', 'ordem')
                    ->withTimestamps();
    }
}