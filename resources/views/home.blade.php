@extends('layout.colaborador')
@section('titulo', 'Home Colaborador - SkillStep')
@section('conteudo') 

<div class="container mt-4">
    <div class="text-center mb-5">
        <h1>Bem-vindo {{ session('usuario_nome', 'SkillStep') }}</h1>
        <p class="lead">Página de colaborador</p>
    </div>
</div>

@endsection
