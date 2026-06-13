<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $usuarioLogado = auth()->user();
        $query = Usuario::query();

        // Regra de Visibilidade: Supervisores não vêem Admins
        if (!$usuarioLogado->isAdmin()) {
            $query->where('tipo_usuario', '!=', 'admin');
        }

        // Filtro de Pesquisa (Nome, E-mail ou CPF)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $search = $request->search;
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        // Filtro por Tipo (admin, supervisor, aluno)
        if ($request->filled('tipo')) {
            $query->where('tipo_usuario', $request->tipo);
        }

        // Filtro por Status (Ativo / Inativo)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $usuarios = $query->latest()->paginate(10);

        $tipos = [
            (object) ['id' => 'admin', 'descricao' => 'Administrador'],
            (object) ['id' => 'supervisor', 'descricao' => 'Supervisor'],
            (object) ['id' => 'aluno', 'descricao' => 'Aluno'],
        ];

        return view('admin.usuarios.index', compact('usuarios', 'tipos'));
    }

    public function store(Request $request)
    {
        // 1. Limpa o CPF (deixa só números)
        $cpfLimpo = preg_replace('/[^0-9]/', '', $request->cpf);

        // 2. Validação Básica
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'senha' => 'required|min:6',
            'tipo_usuario' => 'required|in:admin,supervisor,aluno',
            'cpf' => 'required',
        ], [
            'email.unique' => 'Este e-mail já está sendo usado por outro usuário.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um formato de e-mail válido.',
            'nome.required' => 'O nome é obrigatório.',
            'cpf.required' => 'O CPF é obrigatório.',
            'senha.required' => 'A senha é obrigatória.',
            'senha.min' => 'A senha deve ter pelo menos 6 caracteres.'
        ]);

        // 3. Verifica se o CPF já está cadastrado no banco de dados
        $cpfJaExiste = Usuario::where('cpf', $cpfLimpo)->exists();

        if ($cpfJaExiste) {
            return back()->withErrors(['cpf' => 'Este CPF já está cadastrado no sistema.'])->withInput();
        }

        // 4. Cria o novo usuário capturando o status
        Usuario::create([
            'nome' => $request->nome,
            'cpf' => $cpfLimpo,
            'email' => $request->email,
            'tipo_usuario' => $request->tipo_usuario,
            'status' => $request->boolean('status'), // Captura o valor do checkbox de forma segura
            'senha' => Hash::make($request->senha),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        // 1. Limpa o CPF (deixa só números)
        $cpfLimpo = preg_replace('/[^0-9]/', '', $request->cpf);

        // 2. Substitui o CPF mascarado pelo limpo DENTRO da requisição
        $request->merge(['cpf' => $cpfLimpo]);

        // 3. Validação (Agora o unique vai olhar para o CPF e E-mail corretamente)
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => ['required', Rule::unique('usuarios')->ignore($id)],
            'email' => ['required', 'email', Rule::unique('usuarios')->ignore($id)],
            'tipo_usuario' => 'required|in:admin,supervisor,aluno',
            'senha' => 'nullable|min:6',
        ], [
            'email.unique' => 'Este e-mail já está sendo usado por outro usuário.',
            'cpf.unique' => 'Este CPF já está sendo usado por outro usuário.',
            'nome.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'cpf.required' => 'O CPF é obrigatório.'
        ]);
        // Se o usuário logado tentar desmarcar o status dele mesmo
        if ($usuario->id === auth()->id() && !$request->boolean('status')) {
            return back()->withErrors(['status' => 'Você não pode inativar sua própria conta.'])->withInput();
        }

        // 4. Salva as alterações
        $usuario->nome = $data['nome'];
        $usuario->email = $data['email'];
        $usuario->cpf = $data['cpf'];
        $usuario->tipo_usuario = $data['tipo_usuario'];
        $usuario->status = $request->boolean('status'); // Atualiza o status conforme o switch da modal

        if ($request->filled('senha')) {
            $usuario->senha = Hash::make($request->senha);
        }

        $usuario->save();

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);

        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'Você não pode excluir sua própria conta!');
        }

        try {
            $usuario->delete();

            return redirect()->route('usuarios.index')
                ->with('success', "O usuário {$usuario->nome} foi excluído permanentemente.");

        } catch (\Exception $e) {
            return redirect()->route('usuarios.index')
                ->with('error', 'Erro ao excluir usuário. Verifique se ele não possui planos de estudo ou outros vínculos ativos no sistema.');
        }
    }
}