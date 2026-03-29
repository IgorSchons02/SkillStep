<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TarefaController;
use App\Http\Controllers\TreinamentoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\TrilhaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

// Autenticação
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'autenticar'])->name('login.autenticar');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Grupos de Rotas Protegidas
// Grupos de Rotas Protegidas
Route::middleware(['auth'])->group(function () {

    // Rota curinga que redireciona conforme o perfil
    // Quando você acessar /home, o HomeController decide para onde você vai
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // ── ÁREA DO ADMIN ──
    Route::prefix('admin')->group(function () {
        // A URL final será: /admin/dashboard
        Route::get('/dashboard', [HomeController::class, 'homeAdmin'])->name('homeAdmin');

        Route::resource('tarefas', TarefaController::class);
        Route::resource('treinamentos', TreinamentoController::class);
        Route::resource('usuarios', UsuarioController::class);
        Route::resource('categorias', CategoriaController::class);
        Route::resource('trilhas', TrilhaController::class);
    });

    // ── ÁREA DO SUPERVISOR ──
    Route::prefix('supervisor')->group(function () {
        // A URL final será: /supervisor/dashboard
        Route::get('/dashboard', [HomeController::class, 'homeSupervisor'])->name('homeSupervisor');
    });

    // ── ÁREA DO ALUNO ──
    Route::prefix('aluno')->group(function () {
        // A URL final será: /aluno/dashboard
        Route::get('/dashboard', [HomeController::class, 'homeAluno'])->name('homeAluno');
    });
});
