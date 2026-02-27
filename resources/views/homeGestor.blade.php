@extends('layout.gestor')
@section('titulo', 'Home Gestor - SkillStep')
@section('conteudo') 

<div class="container mt-4">
    <div class="text-center mb-5">
        <h1>Bem-vindo {{ session('usuario_nome', 'SkillStep') }}</h1>
        <p class="lead">Página de administração</p>
    </div>
</div>

@endsection
