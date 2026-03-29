<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trilha extends Model
{
    protected $table = 'trilhas';

    protected $fillable = [
        'nome',
        'descricao',
        'status',
    ];

    /**
     * Relacionamento com Treinamentos, já trazendo na ordem correta da jornada.
     */
    public function treinamentos()
    {
        return $this->belongsToMany(Treinamento::class, 'treinamento_trilha')
            ->withPivot('ordem')
            ->orderBy('treinamento_trilha.ordem', 'asc');
    }
}