<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Exibe a tela de login
     */
    public function index()
    {
        // Se já estiver logado, redireciona para a home correta
        if (Auth::check()) {
            return $this->redirecionarPorTipo(Auth::user());
        }

        return view('login');
    }

    /**
     * Método de autenticação
     */
    public function autenticar(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'senha' => 'required',
        ]);

        // Busca o usuário pelo e-mail
        $usuario = Usuario::where('email', $request->email)->first();

        // 1. Verifica se o usuário existe e se a senha informada é válida
        if ($usuario && Hash::check($request->senha, $usuario->senha)) {

            // 2. Verifica se o usuário está ativo (status == 1 ou true)
            if (!$usuario->status) {
                return back()->with('error', 'Sua conta está inativa. Por favor, entre em contato com o administrador.');
            }

            // 3. Realiza o login e regenera a sessão
            Auth::login($usuario);
            $request->session()->regenerate();

            return $this->redirecionarPorTipo($usuario);
        }

        // Caso o e-mail não exista ou a senha esteja incorreta
        return back()->with('error', 'E-mail ou senha inválidos.');
    }

    /**
     * Helper interno para centralizar a lógica de redirecionamento
     */
    private function redirecionarPorTipo($usuario)
    {
        //dd($usuario->tipo_usuario);
        if ($usuario->isAdmin()) {
            return redirect()->route('homeAdmin')->with('success', 'Bem-vindo, Administrador!');
        }

        if ($usuario->isSupervisor()) {
            return redirect()->route('homeSupervisor')->with('success', 'Painel de Supervisão acessado!');
        }

        // Padrão para Aluno
        return redirect()->route('homeAluno')->with('success', 'Bons estudos, ' . $usuario->nome . '!');
    }

    /**
     * Encerra a sessão de forma segura
     */
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }
}