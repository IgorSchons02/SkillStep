<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tarefa;
use App\Models\Treinamento;
use App\Models\Trilha; // Adicionado para a consulta dinâmica

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

    /**
     * Accessor: Retorna a estrutura em JSON enriquecida com a flag 'descontinuada'
     * Uso: $plano->estrutura_enriquecida
     */
public function getEstruturaEnriquecidaAttribute()
    {
        $estrutura = $this->estrutura;
        
        $trilhasIds = [];
        $treinosIds = [];
        $tarefasIds = [];

        // 1. Mapeia a árvore para coletar todos os IDs das 3 camadas
        if (isset($estrutura['trilhas']) && is_array($estrutura['trilhas'])) {
            foreach ($estrutura['trilhas'] as $trilha) {
                if (isset($trilha['id'])) $trilhasIds[] = $trilha['id'];
                
                if (isset($trilha['treinamentos']) && is_array($trilha['treinamentos'])) {
                    foreach ($trilha['treinamentos'] as $treino) {
                        if (isset($treino['id'])) $treinosIds[] = $treino['id'];
                        
                        if (isset($treino['tarefas']) && is_array($treino['tarefas'])) {
                            foreach ($treino['tarefas'] as $tarefa) {
                                if (isset($tarefa['id'])) $tarefasIds[] = $tarefa['id'];
                            }
                        }
                    }
                }
            }
        }

        // 2. Faz as consultas dinâmicas no banco (apenas se houver IDs)
        $trilhasInativas = !empty($trilhasIds) ? \App\Models\Trilha::whereIn('id', $trilhasIds)->where('status', false)->pluck('id')->toArray() : [];
        $treinosInativos = !empty($treinosIds) ? \App\Models\Treinamento::whereIn('id', $treinosIds)->where('status', false)->pluck('id')->toArray() : [];
        $tarefasInativas = !empty($tarefasIds) ? \App\Models\Tarefa::whereIn('id', $tarefasIds)->where('status', false)->pluck('id')->toArray() : [];

        // 3. Injeta a flag 'descontinuada' nas 3 camadas
        if (isset($estrutura['trilhas']) && is_array($estrutura['trilhas'])) {
            foreach ($estrutura['trilhas'] as &$trilha) {
                if (isset($trilha['id'])) {
                    $trilha['descontinuada'] = in_array($trilha['id'], $trilhasInativas);
                }
                
                if (isset($trilha['treinamentos']) && is_array($trilha['treinamentos'])) {
                    foreach ($trilha['treinamentos'] as &$treino) {
                        if (isset($treino['id'])) {
                            $treino['descontinuada'] = in_array($treino['id'], $treinosInativos);
                        }
                        
                        if (isset($treino['tarefas']) && is_array($treino['tarefas'])) {
                            foreach ($treino['tarefas'] as &$tarefa) {
                                if (isset($tarefa['id'])) {
                                    $tarefa['descontinuada'] = in_array($tarefa['id'], $tarefasInativas);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $estrutura;
    }
}