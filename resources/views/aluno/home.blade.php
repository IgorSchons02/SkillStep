@extends('layout.app')

@section('titulo', 'Início - SkillStep')

@section('content')
<div class="container-fluid py-4">
    
    {{-- Banner de Boas-vindas --}}
    <div class="card border-0 rounded-4 shadow-sm mb-4 bg-gradient-primary text-white overflow-hidden relative">
        <div class="card-body p-4 p-md-5 position-relative z-1">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-2">Olá, {{ explode(' ', Auth::user()->nome)[0] }}! 👋</h2>
                    <p class="fs-5 text-light opacity-75 mb-4">Bem-vindo(a) à sua jornada de desenvolvimento no SkillStep.</p>
                    <a href="{{ route('meus-planos.index') }}" class="btn btn-light text-primary fw-bold px-4 py-2 shadow-sm">
                        <i class="bi bi-play-fill me-1"></i> Acessar Meus Planos
                    </a>
                </div>
                <div class="col-md-4 d-none d-md-block text-end">
                    <i class="bi bi-rocket-takeoff-fill text-white opacity-25" style="font-size: 8rem; position: absolute; right: 5%; top: -10%;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- COLUNA ESQUERDA: Métricas e Atalhos --}}
        <div class="col-lg-8">
            <h5 class="fw-bold text-dark mb-3">Seu Desempenho</h5>
            
            {{-- Cards Totalizadores --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 card-hover h-100 border-bottom border-primary border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted fw-bold text-uppercase small">Total de Planos</span>
                                <div class="icon-box bg-primary-subtle text-primary"><i class="bi bi-journal-bookmark"></i></div>
                            </div>
                            <h2 class="fw-bold text-dark mb-0">{{ $totalPlanos }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 card-hover h-100 border-bottom border-warning border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted fw-bold text-uppercase small">Em Andamento</span>
                                <div class="icon-box bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></div>
                            </div>
                            <h2 class="fw-bold text-dark mb-0">{{ $planosEmAndamento }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 card-hover h-100 border-bottom border-success border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted fw-bold text-uppercase small">Concluídos</span>
                                <div class="icon-box bg-success-subtle text-success"><i class="bi bi-check-circle"></i></div>
                            </div>
                            <h2 class="fw-bold text-dark mb-0">{{ $planosConcluidos }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Guia de Uso do Sistema --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-info-circle-fill text-primary me-2"></i>Como utilizar o SkillStep?</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            <div class="icon-box bg-light text-primary mx-auto mb-3" style="width: 64px; height: 64px; font-size: 2rem;"><i class="bi bi-1-circle"></i></div>
                            <h6 class="fw-bold">Acesse seus Planos</h6>
                            <p class="small text-muted mb-0">Vá em "Meus Planos" para visualizar as jornadas de estudo que foram atribuídas a você.</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="icon-box bg-light text-primary mx-auto mb-3" style="width: 64px; height: 64px; font-size: 2rem;"><i class="bi bi-2-circle"></i></div>
                            <h6 class="fw-bold">Consuma o Conteúdo</h6>
                            <p class="small text-muted mb-0">Abra as Trilhas e Treinamentos, leia as instruções e acesse os links dos materiais.</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="icon-box bg-light text-primary mx-auto mb-3" style="width: 64px; height: 64px; font-size: 2rem;"><i class="bi bi-3-circle"></i></div>
                            <h6 class="fw-bold">Marque como Concluído</h6>
                            <p class="small text-muted mb-0">Após finalizar uma tarefa, marque a caixa de seleção para registrar seu progresso automático.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUNA DIREITA: Supervisores --}}
        <div class="col-lg-4">
            <h5 class="fw-bold text-dark mb-3">Seus Supervisores</h5>
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="text-muted small mb-4">Estes são os profissionais que estão acompanhando o seu desenvolvimento. Procure-os em caso de dúvidas.</p>
                    
                    <div class="list-group list-group-flush">
                        @forelse ($supervisores as $sup)
                            <div class="list-group-item px-0 py-3 border-light d-flex align-items-center">
                                <div class="avatar-circle bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                    {{ strtoupper(substr($sup->nome, 0, 1)) }}
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">{{ $sup->nome }}</h6>
                                    <small class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $sup->email }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 bg-light rounded-3">
                                <i class="bi bi-person-x text-muted fs-2 d-block mb-2"></i>
                                <small class="text-muted">Nenhum supervisor atribuído aos seus planos no momento.</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection