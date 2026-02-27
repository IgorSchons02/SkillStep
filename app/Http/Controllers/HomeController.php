<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Gerencia a página inicial após o login.
     */
    public function index()
    {
        // Verifica se existe um usuário na sessão
        if (!session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $tipo = session('codigo_tipo');

        // Redireciona para o método específico conforme o tipo
        if ($tipo == 1) {
            return $this->homeGestor();
        }

        return $this->homeColaborador();
    }

    /**
     * Visão do Gestor (Tipo 1)
     */
    public function homeGestor()
    {
        // Aqui você buscaria as estatísticas de onboarding no futuro
        return view('homeGestor');
    }

    /**
     * Visão do Colaborador (Tipo 2)
     */
    public function homeColaborador()
    {
        // Aqui você buscaria as tarefas específicas do funcionário
        return view('home'); 
    }
}