@extends('layout.login') {{-- Certifique-se que este layout NÃO tem a sidebar --}}

@section('titulo', 'Login - SkillStep')

@section('conteudo')
    <div class="login-card">
        <div class="login-header">
            <div class="mb-3">
                <i class="bi bi-rocket-takeoff-fill text-primary"
                    style="font-size: 3rem; color: var(--accent) !important;"></i>
            </div>
            <h2>SkillStep</h2>
            <p>Acesse sua plataforma de treinamentos</p>
        </div>

        <form action="{{ route('login.autenticar') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="exemplo@cigam.com.br" required
                    autofocus>
            </div>

            <div class="mb-4">
                <label for="senha" class="form-label fw-semibold">Senha</label>
                <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required>
            </div>

            @if (session('error'))
                <div class="alert alert-danger py-2 small border-0 mb-4">
                    <i class="bi bi-exclamation-circle me-2"></i> {{ session('error') }}
                </div>
            @endif

            <button type="submit" class="btn btn-login w-100 mb-3">
                Entrar no Sistema
            </button>

            <!-- <div class="text-center">
                <a href="#" class="text-decoration-none small text-muted">Esqueceu a senha?</a>
            </div> -->
        </form>
    </div>
@endsection