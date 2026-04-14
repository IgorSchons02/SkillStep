<?php

namespace App\Http\Controllers;

use App\Models\Tarefa;
use App\Models\Categoria;
use Illuminate\Http\Request;

class TarefaController extends Controller
{
    public function index(Request $request)
    {
        $query = Tarefa::with('categoria');

        // Filtro de Texto (Título)
        if ($request->filled('search')) {
            $query->where('titulo', 'like', '%' . $request->search . '%');
        }

        // Filtro de Categoria
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Usamos has() e checamos se não é nulo, pois "0" (Inativo) falharia no filled()
        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        $tarefas = $query->latest()->paginate(10);
        $categorias = Categoria::orderBy('nome')->get();

        return view('admin.tarefas.index', compact('tarefas', 'categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:100|unique:tarefas,titulo',
            'categoria_id' => 'required|exists:categorias,id',
            'tempo_estimado' => 'required|numeric|min:0.1',
            'descricao' => 'required|string|max:2000', // Aceita qualquer texto até 2000 caracteres
            'status' => 'boolean'
        ], [
            'titulo.unique' => 'Já existe uma tarefa cadastrada com este título. Escolha outro nome.',
        ]);
        // Se o checkbox vier vazio do HTML, garantimos que seja false
        $data['status'] = $request->has('status') && $request->status == '1';

        Tarefa::create($data);

        return redirect()->route('tarefas.index')->with('success', 'Tarefa criada com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $tarefa = Tarefa::findOrFail($id);

        $data = $request->validate([
            'titulo' => 'required|string|max:100|unique:tarefas,titulo,' . $id,
            'categoria_id' => 'required|exists:categorias,id',
            'tempo_estimado' => 'required|numeric|min:0.1',
            'descricao' => 'required|string|max:2000', // Aceita qualquer texto até 2000 caracteres
            'status' => 'boolean'
        ], [
            'titulo.unique' => 'Já existe uma tarefa cadastrada com este título. Escolha outro nome.',
        ]);

        $data['status'] = $request->has('status') && $request->status == '1';

        $tarefa->update($data);

        return redirect()->route('tarefas.index')->with('success', 'Tarefa atualizada com sucesso!');
    }

    // public function destroy($id)
    // {
    //     $tarefa = Tarefa::findOrFail($id);

    //     // Trava de Exclusão (Lógica de Relacionamento)

    //     if ($tarefa->treinamentos()->exists()) {
    //         return redirect()->route('tarefas.index')
    //             ->with('error', 'Esta tarefa está vinculada a um ou mais treinamentos e não pode ser excluída. Em vez disso, altere o status para Inativo.');
    //     }

    //     try {
    //         $tarefa->delete();
    //         return redirect()->route('tarefas.index')->with('success', 'Tarefa removida permanentemente.');
    //     } catch (\Exception $e) {
    //         return redirect()->route('tarefas.index')->with('error', 'Erro ao excluir a tarefa.');
    //     }
    // }
    public function destroy($id)
    {
        $tarefa = Tarefa::findOrFail($id);

        //1. Validação Relacional (Se você ainda usa a tabela pivô para templates base)
        if ($tarefa->treinamentos()->exists()) {
            return back()->with('error', 'Não é possível excluir. A tarefa pertence a um treinamento.');
        }

        // 2. Validação no JSON (Planos de Estudo dos alunos)
        $emUsoNoPlano = false;

        \App\Models\Plano::chunk(100, function ($planos) use ($id, &$emUsoNoPlano) {
            foreach ($planos as $plano) {
                $trilhas = $plano->estrutura['trilhas'] ?? [];
                foreach ($trilhas as $trilha) {
                    foreach ($trilha['treinamentos'] ?? [] as $treino) {
                        foreach ($treino['tarefas'] ?? [] as $t) {
                            if (($t['id'] ?? null) == $id) {
                                $emUsoNoPlano = true;
                                return false; // Interrompe a busca imediatamente por performance
                            }
                        }
                    }
                }
            }
        });

        if ($emUsoNoPlano) {
            return back()->with('error', 'Exclusão bloqueada: Esta tarefa está vinculada dentro do plano de estudos de um aluno.');
        }

        $tarefa->delete();
        return redirect()->route('tarefas.index')->with('success', 'Tarefa excluída com sucesso!');
    }
}