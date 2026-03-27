@extends('layout.app')

@section('titulo', 'Dashboard Admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Painel Administrativo</h1>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body">
                    <h6 class="text-primary fw-bold text-uppercase small">Total de Usuários</h6>
                    <div class="h5 mb-0 fw-bold text-gray-800">Gerenciar Alunos e Supervisores</div>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-primary mt-3">Acessar Lista</a>
                </div>
            </div>
        </div>
        </div>
</div>
@endsection