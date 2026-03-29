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
            'titulo' => 'required|string|max:100',
            'categoria_id' => 'required|exists:categorias,id',
            'tempo_estimado' => 'required|numeric|min:0.1',
            'descricao' => 'required|string|max:2000', // Aceita qualquer texto até 2000 caracteres
            'status' => 'boolean'
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
            'titulo' => 'required|string|max:100',
            'categoria_id' => 'required|exists:categorias,id',
            'tempo_estimado' => 'required|numeric|min:0.1',
            'descricao' => 'required|string|max:2000', // Aceita qualquer texto até 2000 caracteres
            'status' => 'boolean'
        ]);

        $data['status'] = $request->has('status') && $request->status == '1';

        $tarefa->update($data);

        return redirect()->route('tarefas.index')->with('success', 'Tarefa atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $tarefa = Tarefa::findOrFail($id);

        // Trava de Exclusão (Lógica de Relacionamento)

        if ($tarefa->treinamentos()->exists()) {
            return redirect()->route('tarefas.index')
                ->with('error', 'Esta tarefa está vinculada a um ou mais treinamentos e não pode ser excluída. Em vez disso, altere o status para Inativo.');
        }

        try {
            $tarefa->delete();
            return redirect()->route('tarefas.index')->with('success', 'Tarefa removida permanentemente.');
        } catch (\Exception $e) {
            return redirect()->route('tarefas.index')->with('error', 'Erro ao excluir a tarefa.');
        }
    }
}