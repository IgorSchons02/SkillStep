<?php

namespace App\Http\Controllers;

use App\Models\Trilha;
use App\Models\Treinamento;
use Illuminate\Http\Request;

class TrilhaController extends Controller
{
    public function index(Request $request)
    {
        // 1. Traz as trilhas e a cadeia completa (Treinamentos -> Tarefas) para poder somar as horas na View
        $query = Trilha::with([
            'treinamentos' => function ($q) {
                $q->with([
                    'tarefas' => function ($q2) {
                        // Filtra para o cálculo de tempo basear-se apenas nas tarefas ativas
                        $q2->where('status', 1);
                    }
                ]);
            }
        ]);

        if ($request->filled('search')) {
            $query->where('nome', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        $trilhas = $query->latest()->paginate(9);

        // Flag virtual de travamento (A ser atrelada à tabela de alunos/matrículas no futuro)
        foreach ($trilhas as $trilha) {
            $trilha->tem_alunos = false;
        }

        // 2. Prepara os Treinamentos Disponíveis para a Modal (Apenas Ativos)
        $treinamentosDisponiveis = Treinamento::with([
            'tarefas' => function ($q) {
                $q->where('status', 1);
            }
        ])->where('status', 1)->get();

        // Mapeia e injeta a "carga_horaria" para o JavaScript ler sem precisar fazer contas complexas
        $treinamentosDisponiveis->map(function ($treinamento) {
            $treinamento->carga_horaria = number_format($treinamento->tarefas->sum('tempo_estimado'), 1, '.', '');
            return $treinamento;
        });

        return view('admin.trilhas.index', compact('trilhas', 'treinamentosDisponiveis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:100|unique:trilhas,nome',
            'descricao' => 'nullable|string|max:255',
            'treinamentos_sequencia' => 'required|string'
        ],[
            'nome.unique' => 'Já existe uma trilha cadastrada com este nome. Escolha outro título.',
        ]);

        $trilha = Trilha::create([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'status' => $request->has('status') && $request->status == '1'
        ]);

        $treinamentosIds = json_decode($request->treinamentos_sequencia, true);

        $syncData = [];
        foreach ($treinamentosIds as $index => $treinamentoId) {
            $syncData[$treinamentoId] = ['ordem' => $index + 1];
        }

        $trilha->treinamentos()->sync($syncData);

        return redirect()->route('trilhas.index')->with('success', 'Trilha de aprendizagem criada com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $trilha = Trilha::findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:100|unique:trilhas,nome,' . $id,
            'descricao' => 'nullable|string|max:255',
            'treinamentos_sequencia' => 'required|string'
        ],[
            'nome.unique' => 'Já existe uma trilha cadastrada com este nome. Escolha outro título.',
        ]);

        $trilha->update([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'status' => $request->has('status') && $request->status == '1'
        ]);

        $treinamentosIds = json_decode($request->treinamentos_sequencia, true);

        $syncData = [];
        foreach ($treinamentosIds as $index => $treinamentoId) {
            $syncData[$treinamentoId] = ['ordem' => $index + 1];
        }

        $trilha->treinamentos()->sync($syncData);

        return redirect()->route('trilhas.index')->with('success', 'Trilha atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $trilha = Trilha::findOrFail($id);

        $emUsoNoPlano = false;

        \App\Models\Plano::chunk(100, function ($planos) use ($id, &$emUsoNoPlano) {
            foreach ($planos as $plano) {
                $trilhas = $plano->estrutura['trilhas'] ?? [];
                foreach ($trilhas as $t) {
                    if (($t['id'] ?? null) == $id) {
                        $emUsoNoPlano = true;
                        return false;
                    }
                }
            }
        });

        if ($emUsoNoPlano) {
            return back()->with('error', 'Exclusão bloqueada: Esta trilha está ativa dentro do Plano de Estudos de um aluno.');
        }

        $trilha->delete();
        return redirect()->route('trilhas.index')->with('success', 'Trilha excluída com sucesso!');
    }
}