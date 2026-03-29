<?php

namespace App\Http\Controllers;

use App\Models\Treinamento;
use App\Models\Tarefa;
use App\Models\Categoria;
use Illuminate\Http\Request;

class TreinamentoController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query principal dos treinamentos
        $query = Treinamento::with('tarefas.categoria'); // Puxa as tarefas e as categorias delas para calcular o tempo total na View

        if ($request->filled('search')) {
            $query->where('nome', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        $treinamentos = $query->latest()->paginate(9); // Paginação de 9 cards (3x3)

        // Simulação da trava de Trilha (No futuro, você fará: $treinamento->trilhas()->exists())
        // Estou injetando um atributo falso para o JS ler na view e bloquear o ícone de excluir tarefas
        foreach ($treinamentos as $treinamento) {
            $treinamento->em_trilha = false; // Mude para true depois quando a tabela Trilhas existir
        }

        // 2. Dados necessários para alimentar a Modal de Criação (O JSON do JavaScript)
        // Só puxamos as ativas para a coluna da esquerda
        $tarefasDisponiveis = Tarefa::with('categoria')->where('status', 1)->get();
        $categorias = Categoria::orderBy('nome')->get();

        return view('admin.treinamentos.index', compact('treinamentos', 'tarefasDisponiveis', 'categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string|max:255',
            'tarefas_sequencia' => 'required|string' // O JSON que vem do Front-end
        ]);

        $treinamento = Treinamento::create([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'status' => $request->has('status') && $request->status == '1'
        ]);

        // Decodifica o Array JSON: "[10, 2, 5]" -> [10, 2, 5]
        $tarefasIds = json_decode($request->tarefas_sequencia, true);

        // Monta o array com a ordem para sincronizar no banco
        $syncData = [];
        foreach ($tarefasIds as $index => $tarefaId) {
            $syncData[$tarefaId] = ['ordem' => $index + 1];
        }

        // Grava as tarefas na tabela intermediária
        $treinamento->tarefas()->sync($syncData);

        return redirect()->route('treinamentos.index')->with('success', 'Treinamento montado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $treinamento = Treinamento::findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string|max:255',
            'tarefas_sequencia' => 'required|string'
        ]);

        $treinamento->update([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'status' => $request->has('status') && $request->status == '1'
        ]);

        $tarefasIds = json_decode($request->tarefas_sequencia, true);

        $syncData = [];
        foreach ($tarefasIds as $index => $tarefaId) {
            $syncData[$tarefaId] = ['ordem' => $index + 1];
        }

        // O sync() inteligentemente apaga as que saíram e insere as novas na ordem certa
        $treinamento->tarefas()->sync($syncData);

        return redirect()->route('treinamentos.index')->with('success', 'Treinamento atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $treinamento = Treinamento::findOrFail($id);

        // Trava para quando você criar as Trilhas

        if ($treinamento->trilhas()->exists()) {
            return redirect()->route('treinamentos.index')->with('error', 'Este treinamento já pertence a uma trilha e não pode ser apagado.');
        }


        try {
            $treinamento->delete();
            return redirect()->route('treinamentos.index')->with('success', 'Treinamento removido com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('treinamentos.index')->with('error', 'Erro ao excluir o treinamento.');
        }
    }
}