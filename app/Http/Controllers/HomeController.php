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
        return view('admin.home');
    }

    /**
     * Dashboard do Supervisor
     * Pasta: resources/views/supervisor/home.blade.php
     */
    public function homeSupervisor()
    {
        // 1. O ID do supervisor logado (convertido para string, pois muitas vezes os selects múltiplos guardam as IDs como strings no JSON)
        $idSupervisor = (string) \Illuminate\Support\Facades\Auth::id();

        // 2. Busca todos os planos onde este supervisor está alocado, trazendo também os dados do aluno
        $planos = \App\Models\Plano::with('aluno')
            ->whereJsonContains('supervisores_ids', $idSupervisor)
            ->get();

        // 3. Calcula as métricas globais para os cards
        $totalPlanos = $planos->count();
        $planosConcluidos = $planos->where('progresso', 100)->count();
        $planosEmAndamento = $totalPlanos - $planosConcluidos;

        // 4. Conta quantos alunos distintos este supervisor acompanha
        $totalAlunos = $planos->pluck('usuario_id')->unique()->count();

        // 5. Separa os planos mais recentes ou em andamento para a tabela de acompanhamento rápido
        $planosAcompanhamento = $planos->sortByDesc('updated_at')->take(8);

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