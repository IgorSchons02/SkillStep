<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Treinamento;
use App\Models\Area;
use App\Models\Tarefa;

class TreinamentoController extends Controller
{
    /**
     * Lista os treinamentos com filtros, ordenação e regra de tenant.
     */
    public function index(Request $request)
    {
        // Traz a área junto para evitar N+1
        //$query = Treinamento::with('area');
        //$query = Treinamento::with('area')->withCount('tarefas');
        $query = Treinamento::with(['area', 'tarefas'])->withCount('tarefas');

        // Filtro de Pesquisa (Texto no Nome ou Descrição)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->search . '%')
                    ->orWhere('descricao', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('ativo', $request->status);
        }

        // Regra de Visibilidade por Área (Multi-tenant)
        $codigoAreaUsuario = session('codigo_area');

        if ($codigoAreaUsuario) {
            // Força o filtro para a área do gestor logado
            $query->where('codigo_area', $codigoAreaUsuario);
            $areas = collect();
        } else {
            // Se for RH/Super Admin, permite o uso do filtro de tela
            if ($request->filled('area')) {
                $query->where('codigo_area', $request->area);
            }
            $areas = Area::all();
        }

        // Ordenação Dinâmica
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_direction', 'asc');
        $colunasPermitidas = ['nome', 'created_at'];

        if (in_array($sortBy, $colunasPermitidas)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->oldest();
        }

        // Paginação
        $treinamentos = $query->paginate(10);
        $codigoAreaUsuario = session('codigo_area');
        if ($codigoAreaUsuario) {
            // Se for gestor, busca só as tarefas da área dele
            $tarefasDisponiveis = Tarefa::where('codigo_area', $codigoAreaUsuario)->orderBy('titulo')->get();
        } else {
            // Se for RH/Super Admin, busca todas as tarefas (trazendo a área junto para exibir na tela)
            $tarefasDisponiveis = Tarefa::with('area')->orderBy('titulo')->get();
        }

        // Não esqueça de adicionar a variável $tarefasDisponiveis no compact()
        return view('gestor.treinamentos.index', compact('treinamentos', 'areas', 'tarefasDisponiveis'));
    }

    /**
     * Salva um novo treinamento no banco.
     */
    public function store(Request $request)
    {
        // 1. Atualizamos a validação para aceitar um array de tarefas
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'codigo_area' => 'required|exists:areas,id',
            'tarefas' => 'nullable|array', // As tarefas vêm em formato de Array dos checkboxes
            'tarefas.*' => 'exists:tarefas,id' // Garante que as tarefas selecionadas realmente existem
        ]);

        // 2. Cria o treinamento normalmente
        $treinamento = Treinamento::create([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'codigo_area' => $request->codigo_area,
            'ativo' => $request->has('ativo') ? true : false,
        ]);

        // 3. --- NOVO: Sincroniza as tarefas na tabela Pivot ---
        if ($request->has('tarefas')) {
            $tarefasSync = [];

            // Faz um loop nas tarefas selecionadas e já cria a "ordem" (1, 2, 3...)
            foreach ($request->tarefas as $index => $tarefaId) {
                $tarefasSync[$tarefaId] = ['ordem' => $index + 1];
            }

            // O sync() salva tudo na tabela 'treinamento_tarefas' em uma única viagem ao banco!
            $treinamento->tarefas()->sync($tarefasSync);
        }

        return redirect()->route('treinamentos.index')->with('success', 'Treinamento criado com sucesso!');
    }

    /**
     * Atualiza os dados de um treinamento existente.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'codigo_area' => 'required|exists:areas,id',
            'tarefas' => 'nullable|array',
            'tarefas.*' => 'exists:tarefas,id'
        ]);

        $treinamento = Treinamento::findOrFail($id);

        if (session('codigo_area') && $treinamento->codigo_area != session('codigo_area')) {
            abort(403, 'Você não tem permissão para editar um treinamento de outra área.');
        }

        $treinamento->update([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'codigo_area' => $request->codigo_area,
            'ativo' => $request->has('ativo') ? true : false,
        ]);

        // --- ATUALIZA AS TAREFAS NA TABELA PIVOT ---
        if ($request->has('tarefas')) {
            $tarefasSync = [];
            foreach ($request->tarefas as $index => $tarefaId) {
                $tarefasSync[$tarefaId] = ['ordem' => $index + 1];
            }
            $treinamento->tarefas()->sync($tarefasSync);
        } else {
            // Se ele desmarcou todas as tarefas, removemos todos os vínculos
            $treinamento->tarefas()->detach();
        }

        return redirect()->route('treinamentos.index')->with('success', 'Treinamento atualizado com sucesso!');
    }

    /**
     * Exclui um treinamento.
     */
    public function destroy($id)
    {
        $treinamento = Treinamento::findOrFail($id);

        if (session('codigo_area') && $treinamento->codigo_area != session('codigo_area')) {
            abort(403, 'Você não tem permissão para excluir um treinamento de outra área.');
        }

        $treinamento->delete();

        return redirect()->route('treinamentos.index')->with('success', 'Treinamento excluído com sucesso!');
    }
}