<?php

namespace App\Http\Controllers;

use App\Models\Plano;
use Illuminate\Http\Request;
use App\Models\Tarefa;
use Illuminate\Support\Facades\Auth;

class MeusPlanosController extends Controller
{
    public function index(Request $request)
    {
        // 1. Busca os planos SOMENTE do usuário logado
        $query = Plano::where('usuario_id', Auth::id());

        // 2. Filtro de Texto (Título do Plano)
        if ($request->filled('search')) {
            $query->where('titulo', 'like', '%' . $request->search . '%');
        }

        // 3. Filtro de Status (Progresso)
        if ($request->filled('status')) {
            if ($request->status === 'concluido') {
                $query->where('progresso', 100);
            } elseif ($request->status === 'andamento') {
                $query->where('progresso', '<', 100);
            }
        }

        $planos = $query->latest()->paginate(9);

        return view('meus_planos.index', compact('planos'));
    }

public function show($id)
    {
        $plano = Plano::findOrFail($id);
        
        // Verifica se é o dono do plano OU se é administrador OU se o ID do logado está nos supervisores
        $idLogado = (string) Auth::id();
        $isSupervisor = is_array($plano->supervisores_ids) && in_array($idLogado, $plano->supervisores_ids);
        
        if ($plano->usuario_id !== Auth::id() && Auth::user()->tipo_usuario !== 'admin' && !$isSupervisor) {
            abort(403, 'Acesso não autorizado a este plano de estudos.');
        }
        
        $tarefasBd = Tarefa::select('id', 'descricao')->get()->keyBy('id');
        
        return view('meus_planos.show', compact('plano', 'tarefasBd'));
    }

    /**
     * MÉTODO AJAX: Atualiza o progresso do aluno em tempo real
     */
    public function updateProgresso(Request $request, $id)
    {
        // 1. Busca o plano e garante que pertence ao aluno logado
        $plano = Plano::where('usuario_id', Auth::id())->findOrFail($id);

        // 2. Recebe a nova estrutura em array (o Laravel converte o JSON do fetch automaticamente)
        $novaEstrutura = $request->input('estrutura');

        // 3. Recalcula o progresso total por segurança (Back-End validation)
        $progressoCalculado = $this->calcularProgresso($novaEstrutura);

        // 4. Salva no banco de dados
        $plano->update([
            'estrutura' => $novaEstrutura,
            'progresso' => $progressoCalculado
        ]);

        // 5. Devolve um JSON avisando o JavaScript que deu tudo certo
        return response()->json([
            'success' => true,
            'progresso' => $progressoCalculado
        ]);
    }

    /**
     * Função privada para varrer a árvore e calcular o percentual de conclusão exato.
     */
    private function calcularProgresso($estrutura)
    {
        $tempoTotal = 0;
        $tempoConcluido = 0;

        if (isset($estrutura['trilhas']) && is_array($estrutura['trilhas'])) {
            foreach ($estrutura['trilhas'] as $trilha) {
                if (isset($trilha['treinamentos']) && is_array($trilha['treinamentos'])) {
                    foreach ($trilha['treinamentos'] as $treino) {
                        if (isset($treino['tarefas']) && is_array($treino['tarefas'])) {
                            foreach ($treino['tarefas'] as $tarefa) {
                                $tempo = isset($tarefa['tempo_estimado']) ? (float) $tarefa['tempo_estimado'] : 0;
                                $tempoTotal += $tempo;

                                if (isset($tarefa['concluido']) && $tarefa['concluido'] == true) {
                                    $tempoConcluido += $tempo;
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($tempoTotal == 0) {
            return 0;
        }

        return (int) round(($tempoConcluido / $tempoTotal) * 100);
    }
}