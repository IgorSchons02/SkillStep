<?php

namespace App\Models;

// Importamos o Authenticatable para que este modelo funcione com o sistema de login
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'codigo_tipo', // 1 para gestor, 2 para colaborador
        'codigo_area'
    ];

    // O Laravel espera que a coluna de senha se chame 'password'. 
    // Como você usa 'senha', precisamos sobrescrever este método para o login funcionar.
    public function getAuthPassword()
    {
        return $this->senha;
    }

    /**
     * Relacionamento: O usuário pertence a uma área.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'codigo_area', 'id');
    }

    /**
     * Helper para verificar se é Gestor (Código 1)
     */
    public function isGestor(): bool
    {
        return $this->codigo_tipo == 1;
    }

    /**
     * Helper para verificar se é Colaborador (Código 2)
     */
    public function isColaborador(): bool
    {
        return $this->codigo_tipo == 2;
    }
}