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
    $credenciais = $request->validate([
        'email' => 'required|email',
        'senha' => 'required', // O Laravel espera o nome 'password', mas vamos ajustar abaixo
    ]);

    // O Laravel nativamente procura por 'password'. Como sua coluna é 'senha':
    $usuario = Usuario::where('email', $request->email)->first();

    if ($usuario && Hash::check($request->senha, $usuario->senha)) {
        Auth::login($usuario);
        $request->session()->regenerate(); // Segurança extra

        return $this->redirecionarPorTipo($usuario);
    }

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