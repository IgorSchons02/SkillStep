<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Categoria::query();

        if ($request->filled('search')) {
            $query->where('nome', 'like', '%' . $request->search . '%')
                  ->orWhere('descricao', 'like', '%' . $request->search . '%');
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
        ]);

        $categoria->update($data);

        return redirect()->route('categorias.index')->with('success', 'Categoria atualizada!');
    }

    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        
        // Impede deletar se houver treinamentos vinculados
        if ($categoria->treinamentos()->count() > 0) {
            return back()->with('error', 'Não é possível excluir uma categoria que possui treinamentos vinculados.');
        }

        $categoria->delete();
        return redirect()->route('categorias.index')->with('success', 'Categoria removida.');
    }
}