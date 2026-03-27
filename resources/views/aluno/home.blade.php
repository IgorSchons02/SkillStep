@extends('layout.app')

@section('titulo', 'Meu Aprendizado')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Bons estudos, {{ Auth::user()->nome }}!</h1>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm">
                <i class="bi bi-info-circle-fill me-2"></i>
                Você ainda não possui um plano de estudos vinculado. Procure seu supervisor!
            </div>
        </div>
    </div>
</div>
@endsection