<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plano;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Ponto de entrada único que redireciona conforme o perfil
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('homeAdmin');
        }

        if ($user->isSupervisor()) {
            return redirect()->route('homeSupervisor');
        }

        return redirect()->route('homeAluno');
    }

    /**
     * Dashboard do Administrador
     * Pasta: resources/views/admin/home.blade.php
     */
    public function homeAdmin()
    {
        // 1. Busca todos os planos apenas para calcular os totalizadores (Cards)
        $todosPlanos = \App\Models\Plano::all();

        $totalPlanos = $todosPlanos->count();
        $planosConcluidos = $todosPlanos->where('progresso', 100)->count();
        $planosEmAndamento = $totalPlanos - $planosConcluidos;

        // 2. Métricas Globais de Usuários e Inventário
        $totalAlunos = \App\Models\Usuario::where('tipo_usuario', 'aluno')->count();
        $totalSupervisores = \App\Models\Usuario::where('tipo_usuario', 'supervisor')->count();
        $totalTrilhas = \App\Models\Trilha::count();
        $totalTreinamentos = \App\Models\Treinamento::count();
        $totalTarefas = \App\Models\Tarefa::count();

        // 3. Busca a lista para a Tabela, ordenando do mais novo pro mais antigo e paginando de 10 em 10
        $planosAcompanhamento = \App\Models\Plano::with('aluno')->latest()->paginate(10);

        return view('admin.home', compact(
            'totalPlanos',
            'planosConcluidos',
            'planosEmAndamento',
            'totalAlunos',
            'totalSupervisores',
            'totalTrilhas',
            'totalTreinamentos',
            'totalTarefas',
            'planosAcompanhamento'
        ));
    }

    /**
     * Dashboard do Supervisor
     * Pasta: resources/views/supervisor/home.blade.php
     */
    public function homeSupervisor()
    {
        $user = Auth::user();

        // 1 & 2. Busca todos os planos onde este supervisor está alocado, trazendo também os dados do aluno.
        // APLICADO O AJUSTE DE SEGURANÇA DE TIPAGEM JSON (Evita painel vazio)
        $planos = \App\Models\Plano::with('aluno')
            ->where(function ($q) use ($user) {
                $q->whereJsonContains('supervisores_ids', $user->id)
                    ->orWhereJsonContains('supervisores_ids', (string) $user->id);
            })
            ->get();

        // 3. Calcula as métricas globais para os cards
        $totalPlanos = $planos->count();
        $planosConcluidos = $planos->where('progresso', 100)->count();
        $planosEmAndamento = $totalPlanos - $planosConcluidos;

        // 4. Conta quantos alunos distintos este supervisor acompanha
        $totalAlunos = $planos->pluck('usuario_id')->unique()->count();

        // 5. AJUSTADO: Pega exatamente 5 planos, ordenados pela criação mais recente
        $planosAcompanhamento = $planos->sortByDesc('created_at')->take(5);

        return view('supervisor.home', compact('totalPlanos', 'planosConcluidos', 'planosEmAndamento', 'totalAlunos', 'planosAcompanhamento'));
    }

    /**
     * Dashboard do Aluno
     * Pasta: resources/views/aluno/home.blade.php
     */
    public function homeAluno()
    {
        $planos = Plano::where('usuario_id', Auth::id())->get();

        // 2. Calcula as métricas
        $totalPlanos = $planos->count();
        $planosConcluidos = $planos->where('progresso', 100)->count();
        $planosEmAndamento = $totalPlanos - $planosConcluidos;

        // 3. Extrai os IDs únicos dos supervisores vinculados a este aluno
        $supervisoresIds = collect();
        foreach ($planos as $plano) {
            if (is_array($plano->supervisores_ids)) {
                $supervisoresIds = $supervisoresIds->merge($plano->supervisores_ids);
            }
        }

        // Remove IDs duplicados caso o mesmo supervisor esteja em vários planos
        $idsUnicos = $supervisoresIds->unique()->values()->toArray();

        // Busca os dados reais dos supervisores
        $supervisores = Usuario::whereIn('id', $idsUnicos)->get(['nome', 'email', 'tipo_usuario']);

        return view('aluno.home', compact('totalPlanos', 'planosConcluidos', 'planosEmAndamento', 'supervisores'));
    }
}