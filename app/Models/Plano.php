<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
    protected $table = 'planos';

    protected $fillable = [
        'usuario_id',
        'titulo',
        'progresso',
        'estrutura',
        'supervisores_ids',
    ];

    /**
     * O Laravel converte automaticamente a coluna JSON do banco 
     * para um Array PHP quando você lê, e de Array para JSON quando você salva.
     */
    protected $casts = [
        'estrutura' => 'array',
        'supervisores_ids' => 'array',
    ];

    /**
     * Relacionamento: Um Plano pertence a um Aluno (Usuário)
     */
    public function aluno()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}