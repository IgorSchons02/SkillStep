<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes; // Importante para o Soft Delete

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    /**
     * Atributos que podem ser preenchidos em massa.
     * Atualizado para refletir a nova migration.
     */
    protected $fillable = [
        'nome',
        'cpf',
        'email',
        'senha',
        'tipo_usuario', // Agora usamos a string direto (admin, supervisor, aluno)
    ];

    /**
     * Atributos escondidos em retornos de API/JSON.
     */
    protected $hidden = [
        'senha',
        'remember_token',
    ];

    /**
     * Informa ao Laravel qual é a coluna de senha para a autenticação.
     */
    public function getAuthPassword()
    {
        return $this->senha;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers de Verificação de Perfil
    |--------------------------------------------------------------------------
    */

    /**
     * Verifica se o usuário é Administrador.
     */
    public function isAdmin(): bool
    {
        return $this->tipo_usuario === 'admin';
    }

    /**
     * Verifica se o usuário é Supervisor.
     */
    public function isSupervisor(): bool
    {
        return $this->tipo_usuario === 'supervisor';
    }

    /**
     * Verifica se o usuário é Aluno.
     */
    public function isAluno(): bool
    {
        return $this->tipo_usuario === 'aluno';
    }

    /**
     * Relacionamento: Um Aluno pode ter vários Supervisores (Many-to-Many).
     * Se você criou a tabela vinculo_supervisao, este método é necessário.
     */
    public function supervisores()
    {
        return $this->belongsToMany(Usuario::class, 'vinculo_supervisao', 'aluno_id', 'supervisor_id');
    }

    /**
     * Relacionamento: Um Supervisor pode ter vários Alunos (Many-to-Many).
     */
    public function alunos()
    {
        return $this->belongsToMany(Usuario::class, 'vinculo_supervisao', 'supervisor_id', 'aluno_id');
    }
}