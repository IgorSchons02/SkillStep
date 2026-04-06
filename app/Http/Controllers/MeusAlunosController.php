<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plano;
use App\Models\Usuario;
use App\Models\Trilha;
use App\Models\Treinamento;
use App\Models\Tarefa;
use Illuminate\Support\Facades\Auth;

class MeusAlunosController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Bloqueia o acesso caso um aluno tente forçar a URL
        if ($user->isAluno()) {
            abort(403, 'Acesso negado. Apenas supervisores e administradores podem acessar esta área.');
        }

        // 1. Inicia a query dos planos trazendo o aluno junto (Eager Loading para performance)
        $query = Plano::with('aluno');

        // 2. REGRA DE NEGÓCIO: Controle de Visibilidade CORRIGIDO
        // INDEPENDENTE de ser Admin ou Supervisor, nesta tela a pessoa só vê os planos 
        // em que ela mesma foi atribuída como supervisora.
        $query->where(function($q) use ($user) {
            $q->whereJsonContains('supervisores_ids', $user->id)
              ->orWhereJsonContains('supervisores_ids', (string) $user->id);
        });

        // 3. FILTRO: Por texto (Nome do Aluno, CPF ou Título do Plano)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'LIKE', "%{$search}%")
                  ->orWhereHas('aluno', function($qAluno) use ($search) {
                      $qAluno->where('nome', 'LIKE', "%{$search}%")
                             ->orWhere('cpf', 'LIKE', "%{$search}%");
                  });
            });
        }

        // 4. FILTRO: Por status (Andamento ou Concluído)
        if ($request->filled('status')) {
            if ($request->status === 'concluido') {
                $query->where('progresso', 100);
            } elseif ($request->status === 'andamento') {
                $query->where('progresso', '<', 100);
            }
        }

        // 5. Executa a query com paginação (9 cards por página)
        $planos = $query->latest()->paginate(9);

        // 6. Dados auxiliares: Buscando nomes para o JavaScript renderizar nos cards
        $supervisores = Usuario::whereIn('tipo_usuario', ['supervisor', 'admin'])->get(['id', 'nome']); 
        
        // 7. Carrega o "DB" de itens para a modal de visualização
        $db = [
            'trilhas'      => Trilha::select('id', 'status')->get(),
            'treinamentos' => Treinamento::select('id', 'status')->get(),
            'tarefas'      => Tarefa::select('id', 'status')->get(),
        ];

        return view('meus_alunos.index', compact('planos', 'supervisores', 'db'));
    }
}