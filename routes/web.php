<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TarefaController;
use App\Http\Controllers\TreinamentoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\TrilhaController;
use App\Http\Controllers\PlanoController;
use App\Http\Controllers\PerfilController;
use App\Http\Middleware\ValidaPerfil; // <-- Importação do seu novo Middleware
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

// Autenticação
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'autenticar'])->name('login.autenticar');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Grupos de Rotas Protegidas (Apenas Logados)
Route::middleware(['auth'])->group(function () {
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::put('/perfil/atualizar-dados', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('/perfil/atualizar-senha', [PerfilController::class, 'updatePassword'])->name('perfil.password');
    // Rota curinga que redireciona conforme o perfil
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // ── ÁREA DO ADMIN ──
    // Adicionamos o middleware exigindo o perfil 'admin'
    Route::prefix('admin')->middleware([ValidaPerfil::class . ':admin'])->group(function () {
        Route::get('/dashboard', [HomeController::class, 'homeAdmin'])->name('homeAdmin');

        Route::resource('tarefas', TarefaController::class);
        Route::resource('treinamentos', TreinamentoController::class);
        Route::resource('usuarios', UsuarioController::class);
        Route::resource('categorias', CategoriaController::class);
        Route::resource('trilhas', TrilhaController::class);
        Route::resource('planos', PlanoController::class);
    });

    // ── ÁREA DO SUPERVISOR ──
    // Adicionamos o middleware exigindo o perfil 'supervisor'
    Route::prefix('supervisor')->middleware([ValidaPerfil::class . ':supervisor'])->group(function () {
        Route::get('/dashboard', [HomeController::class, 'homeSupervisor'])->name('homeSupervisor');
    });

    // ── ÁREA DO ALUNO ──
    // Adicionamos o middleware exigindo o perfil 'aluno'
    Route::prefix('aluno')->middleware([ValidaPerfil::class . ':aluno'])->group(function () {
        Route::get('/dashboard', [HomeController::class, 'homeAluno'])->name('homeAluno');
    });

    // ── ÁREA COMUM (Acessível por todos os logados) ──
    Route::resource('meus-planos', \App\Http\Controllers\MeusPlanosController::class)->only(['index', 'show']);
    Route::post('meus-planos/{id}/progresso', [\App\Http\Controllers\MeusPlanosController::class, 'updateProgresso']);

});