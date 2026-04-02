<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    /**
     * Exibe a tela de perfil do usuário logado.
     */
    public function index()
    {
        // Não precisamos buscar o usuário no banco, o Laravel já disponibiliza via Auth::user()
        return view('perfil.index');
    }

    /**
     * Atualiza os dados básicos do usuário logado (Nome).
     */
    public function update(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        /** @var \App\Models\Usuario $user */
        $user = Auth::user();
        
        $user->nome = $request->nome;
        $user->save();

        return back()->with('success', 'Seus dados foram atualizados com sucesso!');
    }

    /**
     * Atualiza a senha do usuário logado de forma segura.
     */
    public function updatePassword(Request $request)
    {
        // 1. Valida se os campos foram preenchidos corretamente
        $request->validate([
            'senha_atual' => 'required|string',
            // A regra 'confirmed' exige que o HTML tenha um campo chamado 'password_confirmation' (que nós já colocamos na view)
            'password' => 'required|string|min:6|confirmed', 
        ], [
            'password.confirmed' => 'A confirmação da nova senha não confere.',
            'password.min' => 'A nova senha deve ter no mínimo 6 caracteres.'
        ]);

        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        // 2. Verifica se a senha atual digitada bate com a que está salva no banco
        if (!Hash::check($request->senha_atual, $user->password)) {
            // Retorna um erro específico para o campo de senha atual
            return back()->withErrors(['senha_atual' => 'A senha atual está incorreta.']);
        }

        // 3. Criptografa a nova senha e salva no banco
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Sua senha foi atualizada com segurança!');
    }
}