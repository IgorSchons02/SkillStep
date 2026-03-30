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

        $usuarios = $query->latest()->paginate(10);

        $tipos = [
            (object) ['id' => 'admin', 'descricao' => 'Administrador'],
            (object) ['id' => 'supervisor', 'descricao' => 'Supervisor'],
            (object) ['id' => 'aluno', 'descricao' => 'Aluno'],
        ];

        return view('admin.usuarios.index', compact('usuarios', 'tipos'));
    }

    // public function store(Request $request)
    // {
    //     // 1. Limpa o CPF (deixa só números)
    //     $cpfLimpo = preg_replace('/[^0-9]/', '', $request->cpf);

    //     // 2. Validação Manual Inicial (Campos obrigatórios, exceto a regra de unique do CPF por enquanto)
    //     $request->validate([
    //         'nome' => 'required|string|max:255',
    //         'email' => 'required|email',
    //         'senha' => 'required|min:6',
    //         'tipo_usuario' => 'required|in:admin,supervisor,aluno',
    //         'cpf' => 'required', // Apenas checa se preencheu
    //     ]);

    //     // 3. Busca o usuário pelo CPF (mesmo se estiver na lixeira)
    //     $usuarioExistente = Usuario::withTrashed()->where('cpf', $cpfLimpo)->first();

    //     if ($usuarioExistente) {
    //         // CASO A: O usuário está na lixeira (Soft Delete) -> RESTAURAR
    //         if ($usuarioExistente->trashed()) {
    //             $usuarioExistente->restore();
    //             $usuarioExistente->update([
    //                 'nome' => $request->nome,
    //                 'email' => $request->email,
    //                 'tipo_usuario' => $request->tipo_usuario,
    //                 'senha' => Hash::make($request->senha),
    //             ]);

    //             return redirect()->route('usuarios.index')
    //                 ->with('success', 'Usuário reativado com sucesso (CPF recuperado do histórico)!');
    //         }

    //         // CASO B: O usuário está ATIVO -> ERRO DE DUPLICIDADE
    //         // Aqui nós simulamos o erro de validação do Laravel manualmente
    //         return back()->withErrors(['cpf' => 'Este CPF já está cadastrado para um usuário ativo.'])->withInput();
    //     }

    //     // 4. Se não existe (nem ativo, nem deletado), CRIA NOVO
    //     Usuario::create([
    //         'nome' => $request->nome,
    //         'cpf' => $cpfLimpo,
    //         'email' => $request->email,
    //         'tipo_usuario' => $request->tipo_usuario,
    //         'senha' => Hash::make($request->senha),
    //     ]);

    //     return redirect()->route('usuarios.index')->with('success', 'Usuário cadastrado com sucesso!');
    // }

    //sem soft delete
    public function store(Request $request)
    {
        // 1. Limpa o CPF (deixa só números)
        $cpfLimpo = preg_replace('/[^0-9]/', '', $request->cpf);

        // 2. Validação Básica
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email', // Adicionei a validação de e-mail único por segurança
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
            // Retorna o erro exatamente no campo CPF para o usuário ver
            return back()->withErrors(['cpf' => 'Este CPF já está cadastrado no sistema.'])->withInput();
        }

        // 4. Se não existe, cria o novo usuário normalmente
        Usuario::create([
            'nome' => $request->nome,
            'cpf' => $cpfLimpo,
            'email' => $request->email,
            'tipo_usuario' => $request->tipo_usuario,
            'senha' => \Illuminate\Support\Facades\Hash::make($request->senha),
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
            'cpf' => ['required', \Illuminate\Validation\Rule::unique('usuarios')->ignore($id)],
            'email' => ['required', 'email', \Illuminate\Validation\Rule::unique('usuarios')->ignore($id)],
            'tipo_usuario' => 'required|in:admin,supervisor,aluno',
            'senha' => 'nullable|min:6',
        ], [
            'email.unique' => 'Este e-mail já está sendo usado por outro usuário.',
            'cpf.unique' => 'Este CPF já está sendo usado por outro usuário.',
            'nome.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'cpf.required' => 'O CPF é obrigatório.'
        ]);

        // 4. Salva as alterações
        $usuario->nome = $data['nome'];
        $usuario->email = $data['email'];
        $usuario->cpf = $data['cpf']; // Já está limpo pois fizemos o merge
        $usuario->tipo_usuario = $data['tipo_usuario'];

        if ($request->filled('senha')) {
            $usuario->senha = \Illuminate\Support\Facades\Hash::make($request->senha);
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