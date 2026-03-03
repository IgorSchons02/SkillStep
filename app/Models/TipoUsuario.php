<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoUsuario extends Model
{
    use HasFactory;

    // Define explicitamente o nome da tabela
    protected $table = 'tipo_usuarios';

    // Campos que podem ser preenchidos em massa
    protected $fillable = [
        'descricao',
    ];

    /**
     * Relacionamento 1:N com Usuários.
     * Um tipo de usuário (ex: Gestor) pode pertencer a vários usuários.
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'codigo_tipo', 'id');
    }
}