<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Tarefa;
use App\Models\Area;

class TarefaController extends Controller
{
    public function index(Request $request)
    {
        $query = Tarefa::with('area'); // Eager loading para evitar o problema N+1

        // Filtro de Pesquisa (Texto)
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('titulo', 'like', '%' . $request->search . '%')
                  ->orWhere('descricao', 'like', '%' . $request->search . '%');
            });
        }

        // Regra de Visibilidade por Área (Multi-tenant)
        $codigoAreaUsuario = session('codigo_area');

        if ($codigoAreaUsuario) {
            // Se o usuário tem uma área, FORÇA o filtro e ignora o que vier da URL
            $query->where('codigo_area', $codigoAreaUsuario);
            
            // Retorna uma coleção vazia de áreas, já que ele não pode usar o filtro
            $areas = collect(); 
        } else {
            // Se for nulo (RH / Super Admin), permite usar o filtro da tela
            if ($request->filled('area')) {
                $query->where('codigo_area', $request->area);
            }
            
            // Busca todas as áreas para preencher o combobox
            $areas = Area::all();
        }

        // Paginação: traz apenas 10 registros por vez do banco
        //$tarefas = $query->orderBy('titulo')->paginate(10);
        //$tarefas = $query->oldest()->paginate(10); pela mais antiga

        // --- ORDENAÇÃO DINÂMICA ---
        // Pega os parâmetros da URL ou define um padrão (Data mais antiga primeiro)
        $sortBy = $request->input('sort_by', 'created_at'); // Se a sua coluna no banco chamar data_criacao, troque aqui
        $sortDir = $request->input('sort_direction', 'asc'); 

        // Lista de colunas permitidas para evitar falhas de segurança (SQL Injection)
        $colunasPermitidas = ['titulo', 'created_at'];

        if (in_array($sortBy, $colunasPermitidas)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->oldest(); // Fallback de segurança
        }

        // Paginação: traz os registros já filtrados e ordenados
        $tarefas = $query->paginate(10);

        return view('gestor.tarefas.index', compact('tarefas', 'areas'));
    }

    public function store(Request $request)
    {
        // 1. Validação dos dados que vieram do formulário
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'codigo_area' => 'required|exists:areas,id'
        ]);

        // 2. Cria a tarefa no banco de dados usando os campos preenchidos
        Tarefa::create([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'codigo_area' => $request->codigo_area,
            // A data de criação o Laravel já preenche automaticamente no created_at
        ]);

        // 3. Redireciona de volta para a tela de listagem com uma mensagem de sucesso
        return redirect()->route('tarefas.index')->with('success', 'Tarefa cadastrada com sucesso!');
    }

    public function update(Request $request, $id)
    {
        // 1. Validação básica
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'codigo_area' => 'required|exists:areas,id'
        ]);

        // 2. Busca a tarefa pelo ID e lança erro 404 se não achar
        $tarefa = Tarefa::findOrFail($id);

        // 3. Regra de Segurança Extra: Garante que um Gestor não edite tarefas de outra área
        if (session('codigo_area') && $tarefa->codigo_area != session('codigo_area')) {
            abort(403, 'Você não tem permissão para editar uma tarefa de outra área.');
        }

        // 4. Atualiza os dados no banco
        $tarefa->update([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'codigo_area' => $request->codigo_area,
        ]);

        return redirect()->route('tarefas.index')->with('success', 'Tarefa atualizada com sucesso!');
    }

    public function destroy($id)
    {
        // 1. Busca a tarefa pelo ID
        $tarefa = Tarefa::findOrFail($id);

        // 2. Regra de Segurança: Impede que um gestor exclua tarefas de outras áreas
        if (session('codigo_area') && $tarefa->codigo_area != session('codigo_area')) {
            abort(403, 'Você não tem permissão para excluir uma tarefa de outra área.');
        }

        // 3. Exclui o registro do banco de dados
        $tarefa->delete();

        // 4. Redireciona de volta com a mensagem de sucesso
        return redirect()->route('tarefas.index')->with('success', 'Tarefa excluída com sucesso!');
    }
}