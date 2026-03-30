<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        // 1. Adicionamos o withCount('tarefas'). 
        // Isso cria a propriedade mágica $cat->tarefas_count que a sua View está esperando!
        $query = Categoria::withCount('tarefas');

        if ($request->filled('search')) {
            // 2. Agrupamos o orWhere dentro de uma função para garantir que a busca não quebre outras lógicas do SQL
            $query->where(function($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->search . '%')
                  ->orWhere('descricao', 'like', '%' . $request->search . '%');
            });
        }

        $categorias = $query->latest()->paginate(10);

        return view('admin.categorias.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|unique:categorias|max:50',
            'descricao' => 'nullable|max:255',
            'cor_hex' => 'required|max:7'
        ],[
            'nome.unique' => 'Já existe uma categoria cadastrada com este nome.',
            'nome.required' => 'O nome da categoria é obrigatório.',
            'nome.max' => 'O nome não pode ter mais de 50 caracteres.',
            'cor_hex.required' => 'A cor é obrigatória.'
        ]);

        Categoria::create($data);

        return redirect()->route('categorias.index')->with('success', 'Categoria criada com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        $data = $request->validate([
            'nome' => 'required|max:50|unique:categorias,nome,' . $id,
            'descricao' => 'nullable|max:255',
            'cor_hex' => 'required|max:7'
        ],[
            'nome.unique' => 'Já existe uma categoria cadastrada com este nome.',
            'nome.required' => 'O nome da categoria é obrigatório.',
            'nome.max' => 'O nome não pode ter mais de 50 caracteres.',
            'cor_hex.required' => 'A cor é obrigatória.'
        ]);

        $categoria->update($data);

        return redirect()->route('categorias.index')->with('success', 'Categoria atualizada!');
    }

    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        
        // 3. Trocamos count() > 0 por exists(). 
        // O exists() é mais rápido no banco de dados, pois ele para a busca assim que acha a primeira tarefa, em vez de contar todas.
        if ($categoria->tarefas()->exists()) {
            return back()->with('error', 'Não é possível excluir uma categoria que possui tarefas vinculadas.');
        }

        $categoria->delete();
        return redirect()->route('categorias.index')->with('success', 'Categoria removida.');
    }
}