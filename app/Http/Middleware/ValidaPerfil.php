<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidaPerfil
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $perfilEsperado (admin, supervisor ou aluno)
     */
    public function handle(Request $request, Closure $next, string $perfilEsperado): Response
    {
        // Verifica se o tipo de usuário logado bate com o exigido pela rota
        if (Auth::check() && Auth::user()->tipo_usuario !== $perfilEsperado) {
            // Se tentar acessar área restrita, exibe erro 403 (Acesso Negado)
            abort(403, 'Acesso negado. Você não tem permissão para acessar esta área do SkillStep.');
        }

        return $next($request);
    }
}