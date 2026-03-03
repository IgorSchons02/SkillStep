<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TarefaController; 
use App\Http\Controllers\TreinamentoController; 
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Autenticação
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'autenticar'])->name('login.autenticar');

// Home / Dashboard
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/gestor/dashboard', [HomeController::class, 'homeGestor'])->name('homeGestor');
Route::get('/colaborador/treinamento', [HomeController::class, 'homeColaborador'])->name('homeColaborador');

// ── Gestão de Tarefas (Exclusivo Gestor) ──
// Seguindo seu padrão de nomes e estrutura de pastas
Route::prefix('gestor')->group(function () {
    Route::get('/tarefas', [TarefaController::class, 'index'])->name('tarefas.index');
    Route::post('/tarefas', [TarefaController::class, 'store'])->name('tarefas.store');
    Route::put('/tarefas/{id}', [TarefaController::class, 'update'])->name('tarefas.update');
    Route::delete('/tarefas/{id}', [TarefaController::class, 'destroy'])->name('tarefas.destroy');
    // Rotas de Treinamentos
    Route::get('/treinamentos', [TreinamentoController::class, 'index'])->name('treinamentos.index');
    Route::post('/treinamentos', [TreinamentoController::class, 'store'])->name('treinamentos.store');
    Route::put('/treinamentos/{id}', [TreinamentoController::class, 'update'])->name('treinamentos.update');
    Route::delete('/treinamentos/{id}', [TreinamentoController::class, 'destroy'])->name('treinamentos.destroy');
    // Rotas de Usuários   
    Route::get('/usuarios', [App\Http\Controllers\UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [App\Http\Controllers\UsuarioController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{id}', [App\Http\Controllers\UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [App\Http\Controllers\UsuarioController::class, 'destroy'])->name('usuarios.destroy');
});

Route::post('/logout', function () {
    Auth::logout();
    session()->flush(); // Limpa a sessão manual que você criou
    return redirect('/login');
})->name('logout');