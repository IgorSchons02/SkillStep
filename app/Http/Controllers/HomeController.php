<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Ponto de entrada único que redireciona conforme o perfil
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('homeAdmin');
        }

        if ($user->isSupervisor()) {
            return redirect()->route('homeSupervisor');
        }

        return redirect()->route('homeAluno');
    }

    /**
     * Dashboard do Administrador
     * Pasta: resources/views/admin/home.blade.php
     */
    public function homeAdmin()
    {
        return view('admin.home');
    }

    /**
     * Dashboard do Supervisor
     * Pasta: resources/views/supervisor/home.blade.php
     */
    public function homeSupervisor()
    {
        return view('supervisor.home');
    }

    /**
     * Dashboard do Aluno
     * Pasta: resources/views/aluno/home.blade.php
     */
    public function homeAluno()
    {
        // Aqui você pode buscar o plano de estudos do aluno logado futuramente
        return view('aluno.home');
    }
}