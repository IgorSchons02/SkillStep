<?php

namespace App\Http\Controllers;

use App\Models\Plano;
use App\Models\Usuario;
use App\Models\Trilha;
use App\Models\Treinamento;
use App\Models\Tarefa;
use Illuminate\Http\Request;

class PlanoController extends Controller
{
    public function index(Request $request)
    {
        // 1. Busca os planos existentes com os dados do aluno
        $query = Plano::with('aluno');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('titulo', 'like', "%{$search}%")
                  ->orWhereHas('aluno', function ($q) use ($search) {
                      $q->where('nome', 'like', "%{$search}%")
                        ->orWhere('cpf', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
        }

        if ($request->filled('status')) {
            if ($request->status === 'concluido') {
                $query->where('progresso', 100);
            } elseif ($request->status === 'andamento') {
                $query->where('progresso', '<', 100);
            }
        }

        $planos = $query->latest()->paginate(9);
        

        // 2. Busca todos os usuários para o Select2 (Como solicitado, qualquer tipo de usuário pode ser aluno)
        $supervisores = Usuario::whereIn('tipo_usuario', ['supervisor', 'admin'])->orderBy('nome')->get(['id', 'nome']);
        $alunos = Usuario::orderBy('nome')->get(['id', 'nome', 'email', 'cpf']);

        // 3. Monta o objeto "DB" para o JavaScript da View consumir
        // Puxamos tudo (ativos e inativos) pois um plano antigo pode conter itens que hoje estão inativos
        $db = [
            'tarefas' => Tarefa::select('id', 'titulo', 'tempo_estimado', 'status')->get()->toArray(),
            
            'treinamentos' => Treinamento::with('tarefas:id')->get()->map(function($treino) {
                return [
                    'id' => $treino->id,
                    'titulo' => $treino->nome, // O JS espera 'titulo'
                    'status' => $treino->status,
                    'tarefas' => $treino->tarefas->pluck('id')->toArray()
                ];
            })->toArray(),
            
            'trilhas' => Trilha::with('treinamentos:id')->get()->map(function($trilha) {
                return [
                    'id' => $trilha->id,
                    'titulo' => $trilha->nome, // O JS espera 'titulo'
                    'status' => $trilha->status,
                    'treinamentos' => $trilha->treinamentos->pluck('id')->toArray()
                ];
            })->toArray()
        ];

        return view('admin.planos.index', compact('planos', 'alunos','supervisores', 'db'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:150|unique:planos,titulo',
            'usuario_id' => 'required|exists:usuarios,id',
            'estrutura' => 'required|string', // Recebemos como string JSON do front-end
            'supervisores' => 'array'
        ],[
            'titulo.unique' => 'Já existe um plano de estudos cadastrado com este título. Escolha outro título.',
        ]);

        // Transforma o JSON string em Array PHP
        $estruturaArray = json_decode($request->estrutura, true);

        // Segurança Back-End: Recalcula o progresso real ignorando o que o JS mandou
        $progressoCalculado = $this->calcularProgresso($estruturaArray);

        Plano::create([
            'titulo' => $request->titulo,
            'usuario_id' => $request->usuario_id,
            'progresso' => $progressoCalculado,
            'estrutura' => $estruturaArray, // O Laravel faz o cast para JSON automaticamente graças ao Model
            'supervisores_ids' => $request->supervisores
        ]);

        return redirect()->route('planos.index')->with('success', 'Plano de estudos atribuído com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $plano = Plano::findOrFail($id);

        $request->validate([
            'titulo' => 'required|string|max:150|unique:planos,titulo,' . $id,
            'usuario_id' => 'required|exists:usuarios,id',
            'estrutura' => 'required|string',
            'supervisores' => 'array'
        ],[
            'titulo.unique' => 'Já existe um plano de estudos cadastrado com este título. Escolha outro título.',
        ]);

        $estruturaArray = json_decode($request->estrutura, true);
        $progressoCalculado = $this->calcularProgresso($estruturaArray);

        $plano->update([
            'titulo' => $request->titulo,
            'usuario_id' => $request->usuario_id,
            'progresso' => $progressoCalculado,
            'estrutura' => $estruturaArray,
            'supervisores_ids' => $request->supervisores
        ]);

        return redirect()->route('planos.index')->with('success', 'Plano de estudos atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $plano = Plano::findOrFail($id);

        try {
            $plano->delete();
            return redirect()->route('planos.index')->with('success', 'Plano de estudos excluído.');
        } catch (\Exception $e) {
            return redirect()->route('planos.index')->with('error', 'Erro ao excluir o plano de estudos.');
        }
    }

    /**
     * Exibe o Plano para o Aluno ou Supervisor.
     */
    public function show($id)
    {
        $plano = Plano::with('aluno')->findOrFail($id);

        // Ao invés de mandar a 'estrutura' crua, chamamos o accessor que criamos na Model
        $estruturaEnriquecida = $plano->estrutura_enriquecida;

        return view('planos.show', compact('plano', 'estruturaEnriquecida'));
    }

    /**
     * Função privada para varrer a árvore JSON e calcular o percentual de conclusão exato.
     * Regra: Soma de (tempo_estimado das tarefas concluídas) / (tempo_estimado de todas as tarefas) * 100
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