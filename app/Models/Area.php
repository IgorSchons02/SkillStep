<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    // Define o nome da tabela no banco de dados
    protected $table = 'areas';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['name', 'slug'];

    /**
     * Relacionamento: Uma área possui muitos usuários.
     */
    public function usuarios(): HasMany
    {
        // Relaciona com a Model Usuario usando a FK 'codigo_area'
        return $this->hasMany(Usuario::class, 'codigo_area', 'id');
    }
}