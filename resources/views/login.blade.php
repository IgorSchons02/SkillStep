@extends('layout.login')

@section('titulo', 'Login - SkillStep')

@section('conteudo')
    <!-- <div class="text-center mb-4">
        <img src="{{ asset('images/LogoEasy.png') }}" alt="Logo" class="logo">
    </div> -->

    <form action="{{ route('login.autenticar') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Usuário:</label>
            <input type="text" id="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="senha" class="form-label">Senha:</label>
            <input type="password" id="senha" name="senha" class="form-control" required>
        </div>

        @if (session('erro'))
            <div class="alert alert-danger mt-2">{{ session('erro') }}</div>
        @endif

        <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>
@endsection
