<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Area;
use App\Models\TipoUsuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    /**
     * Lista os usuários com filtros, ordenação e regra de tenant.
     */
public function index(Request $request)
    {
        // 1. Pegamos quem está logado logo no início
        $tipoUsuarioLogado = session('codigo_tipo');

        // Traz as relações para evitar o problema de N+1 queries no banco
        $query = Usuario::with(['area', 'tipo']);

        // --- NOVO: Trava de Visibilidade ---
        // Se o usuário logado NÃO for Admin (3), esconde os Admins da listagem principal
        if ($tipoUsuarioLogado != 3) {
            $query->where('codigo_tipo', '!=', 3);
        }

        // Filtro de Pesquisa (Texto no Nome ou E-mail)
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filtro de Área via formulário da tela
        if ($request->filled('area')) {
            if ($request->area === 'sem_area') {
                // Traz apenas os colaboradores globais (sem área)
                $query->whereNull('codigo_area');
            } else {
                $query->where('codigo_area', $request->area);
            }
        }

        // Como a trava foi removida, todos precisam ver o combo de áreas para conseguir filtrar
        $areas = Area::orderBy('name')->get();

        // Ordenação padrão: mais recentes primeiro
        $usuarios = $query->latest()->paginate(10);

        // --- Filtro de Tipos de Usuário para o Modal de Criação ---
        if ($tipoUsuarioLogado == 3) {
            // Se for Admin (3), pode ver e criar qualquer tipo de usuário
            $tipos = TipoUsuario::all();
        } else {
            // Se for Gestor (1), só pode criar Gestores (1) ou Colaboradores (2)
            $tipos = TipoUsuario::whereIn('id', [1, 2])->get();
        }

        return view('gestor.usuarios.index', compact('usuarios', 'areas', 'tipos'));
    }

    /**
     * Salva um novo usuário no banco de dados.
     */
    public function store(Request $request)
    {
        // 1. Validação dos dados
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email|max:255',
            'senha' => 'required|string|min:6', // Mínimo de 6 caracteres para a senha
            'codigo_tipo' => 'required|exists:tipo_usuarios,id',
            'codigo_area' => 'nullable|exists:areas,id'
        ], [
            // Mensagens customizadas (opcional)
            'email.unique' => 'Este e-mail já está cadastrado no sistema.'
        ]);

        $tipoUsuarioLogado = session('codigo_tipo');
        
        // Se quem está logado NÃO é admin, mas tentou enviar o código 3 (Admin)
        if ($tipoUsuarioLogado != 3 && $request->codigo_tipo == 3) {
            return redirect()->back()->with('error', 'Apenas Administradores podem criar novos Administradores.');
        }
        // 2. Regra de Negócio: Se não for Gestor (ID 1), forçamos a área a ser nula
        $codigoArea = $request->codigo_tipo == 1 ? $request->codigo_area : null;

        // 3. Criação do usuário criptografando a senha
        Usuario::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'senha' => \Illuminate\Support\Facades\Hash::make($request->senha),
            'codigo_tipo' => $request->codigo_tipo,
            'codigo_area' => $codigoArea,
        ]);

        // 4. Redirecionamento com mensagem de sucesso
        return redirect()->route('usuarios.index')->with('success', 'Usuário cadastrado com sucesso!');
    }

    /**
     * Atualiza os dados de um usuário existente.
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
        $tipoUsuarioLogado = session('codigo_tipo');

        // --- TRAVA DE SEGURANÇA BACKEND ---
        // 1. Gestor não pode editar um Admin
        if ($tipoUsuarioLogado != 3 && $usuario->codigo_tipo == 3) {
            return redirect()->back()->with('error', 'Você não tem permissão para editar um Administrador.');
        }
        // 2. Gestor não pode transformar alguém em Admin
        if ($tipoUsuarioLogado != 3 && $request->codigo_tipo == 3) {
            return redirect()->back()->with('error', 'Apenas Administradores podem promover outros a Administrador.');
        }

        // --- VALIDAÇÃO COM EXCEÇÃO DE E-MAIL ---
        $request->validate([
            'nome' => 'required|string|max:255',
            // A regra abaixo diz: o e-mail deve ser único na tabela usuarios, EXCETO para a linha com este $id
            'email' => 'required|email|max:255|unique:usuarios,email,' . $id,
            'senha' => 'nullable|string|min:6', // Nullable significa que não é obrigatório preencher
            'codigo_tipo' => 'required|exists:tipo_usuarios,id',
            'codigo_area' => 'nullable|exists:areas,id'
        ]);

        // Regra de Negócio: Se não for Gestor (ID 1), forçamos a área a ser nula
        $codigoArea = $request->codigo_tipo == 1 ? $request->codigo_area : null;

        // Prepara os dados básicos
        $dadosAtualizacao = [
            'nome' => $request->nome,
            'email' => $request->email,
            'codigo_tipo' => $request->codigo_tipo,
            'codigo_area' => $codigoArea,
        ];

        // Se o campo de senha foi preenchido, nós criptografamos e adicionamos aos dados de atualização
        if ($request->filled('senha')) {
            $dadosAtualizacao['senha'] = \Illuminate\Support\Facades\Hash::make($request->senha);
        }

        $usuario->update($dadosAtualizacao);

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Exclui um utilizador do sistema.
     */
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        
        $usuarioLogadoId = session('usuario_id');
        $tipoUsuarioLogado = session('codigo_tipo');

        // --- TRAVAS DE SEGURANÇA ---
        
        // 1. O utilizador não pode excluir a própria conta
        if ($usuario->id == $usuarioLogadoId) {
            return redirect()->back()->with('error', 'Você não pode excluir a sua própria conta.');
        }

        // 2. Um Gestor não pode excluir um Administrador
        if ($tipoUsuarioLogado != 3 && $usuario->codigo_tipo == 3) {
            return redirect()->back()->with('error', 'Você não tem permissão para excluir um Administrador.');
        }

        // Se passou pelas travas, exclui o utilizador
        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Utilizador excluído com sucesso!');
    }
}