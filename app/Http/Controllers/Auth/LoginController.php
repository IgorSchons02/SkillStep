<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Exibe a tela de login
     */
    public function index()
    {
        return view('login');
    }

    /**
     * Método de autenticação baseado em select direto
     */
    public function autenticar(Request $request)
    {
        // 1. Coleta os dados do formulário
        $email = $request->input('email');
        $senha = $request->input('senha');

        // 2. Realiza o select direto no banco de dados
        $usuario = Usuario::where('email', $email)
                          ->where('senha', $senha)
                          ->first();

        // 3. Verifica se o usuário foi encontrado
        if ($usuario) {
            // Armazena os dados na sessão (ajustado para o seu ER)
            session([
                'usuario_id'   => $usuario->id,
                'usuario_nome' => $usuario->nome,
                'codigo_tipo'  => $usuario->codigo_tipo, // 1 ou 2
                'codigo_area'  => $usuario->codigo_area,
            ]);

            // Redireciona conforme o tipo de usuário
            if ($usuario->codigo_tipo == 1) {
                return redirect()->route('homeGestor')->with('success', 'Bem-vindo, Gestor!');
            } else {
                return redirect()->route('homeColaborador')->with('success', 'Bem-vindo ao seu treinamento!');
            }

        } else {
            // Retorna com erro caso falhe
            return redirect()->route('login')->with('error', 'E-mail ou senha inválidos.');
        }
    }

    /**
     * Encerra a sessão
     */
    public function logout()
    {
        session()->flush();
        return redirect()->route('login');
    }
}