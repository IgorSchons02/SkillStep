<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use SoftDeletes;

    protected $table = 'categorias';

    protected $fillable = [
        'nome',
        'descricao',
        'cor_hex'
    ];

    /**
     * Uma categoria possui muitos treinamentos
     */
    public function treinamentos()
    {
        return $this->hasMany(Treinamento::class);
    }
}