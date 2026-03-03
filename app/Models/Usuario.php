<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'codigo_tipo', 
        'codigo_area'
    ];

    // Trava de segurança para não vazar a senha em retornos JSON
    protected $hidden = [
        'senha',
    ];

    // Informa ao Laravel qual é a coluna de senha no momento do Login
    public function getAuthPassword()
    {
        return $this->senha;
    }

    /**
     * Relacionamento: O usuário tem um Tipo (Gestor, Colaborador, etc).
     */
    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoUsuario::class, 'codigo_tipo', 'id');
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
    
    /**
     * Helper para verificar se é Admin (Código 3)
     */
    public function isAdmin(): bool
    {
        return $this->codigo_tipo == 3;
    }
}