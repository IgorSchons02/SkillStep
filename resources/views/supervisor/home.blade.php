@extends('layout.app')

@section('titulo', 'Painel do Supervisor')

@section('content')
<div class="container-fluid py-4">
    
    {{-- Banner de Boas-vindas --}}
    <div class="card border-0 rounded-4 shadow-sm mb-4 bg-gradient-supervisor text-white overflow-hidden relative">
        <div class="card-body p-4 p-md-5 position-relative z-1 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-2">Painel de Supervisão</h2>
                <p class="fs-5 text-light opacity-75 mb-0">Acompanhe o desenvolvimento dos seus alunos em tempo real.</p>
            </div>
            <div class="d-none d-md-block opacity-50">
                <i class="bi bi-bar-chart-fill" style="font-size: 5rem;"></i>
            </div>
        </div>
    </div>

    {{-- Cards de Métricas (Visibilidade do Estado do Sistema) --}}
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm card-hover h-100 border-start border-primary border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-dash bg-primary-subtle text-primary me-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted fw-bold text-uppercase small d-block">Alunos Ativos</span>
                        <h3 class="fw-bold text-dark mb-0">{{ $totalAlunos }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm card-hover h-100 border-start border-info border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-dash bg-info-subtle text-info me-3">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <div>
                        <span class="text-muted fw-bold text-uppercase small d-block">Total de Planos</span>
                        <h3 class="fw-bold text-dark mb-0">{{ $totalPlanos }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm card-hover h-100 border-start border-warning border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-dash bg-warning-subtle text-warning me-3">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted fw-bold text-uppercase small d-block">Em Andamento</span>
                        <h3 class="fw-bold text-dark mb-0">{{ $planosEmAndamento }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm card-hover h-100 border-start border-success border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-dash bg-success-subtle text-success me-3">
                        <i class="bi bi-check-all"></i>
                    </div>
                    <div>
                        <span class="text-muted fw-bold text-uppercase small d-block">Concluídos</span>
                        <h3 class="fw-bold text-dark mb-0">{{ $planosConcluidos }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de Acompanhamento Recente --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-activity text-primary me-2"></i>Acompanhamento Recente</h5>
            <a href="{{ route('meus-alunos.index') }}" class="btn btn-sm btn-outline-secondary">Ver Todos os Alunos</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3 border-bottom-0" style="width: 30%">Aluno</th>
                            <th class="py-3 border-bottom-0" style="width: 35%">Plano de Estudos</th>
                            <th class="py-3 border-bottom-0" style="width: 20%">Progresso</th>
                            <th class="text-end pe-4 py-3 border-bottom-0">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse ($planosAcompanhamento as $plano)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-light text-secondary fw-bold rounded-circle d-flex align-items-center justify-content-center me-3 border" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($plano->aluno->nome ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $plano->aluno->nome ?? 'Aluno Removido' }}</div>
                                            <div class="text-muted small">{{ $plano->aluno->email ?? '--' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">{{ $plano->titulo }}</div>
                                    <div class="text-muted small"><i class="bi bi-calendar-event me-1"></i>Atualizado em {{ $plano->updated_at->format('d/m/Y') }}</div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small fw-bold {{ $plano->progresso == 100 ? 'text-success' : 'text-primary' }}">{{ $plano->progresso }}%</span>
                                        @if($plano->progresso == 100)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Finalizado</span>
                                        @endif
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{ $plano->progresso == 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $plano->progresso }}%"></div>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    {{-- LINK ATUALIZADO COM O PARÂMETRO 'abrir_modal' --}}
                                    <a href="{{ route('meus-alunos.index', ['abrir_modal' => $plano->id]) }}" class="btn btn-sm btn-light border text-primary fw-bold" title="Abrir painel de acompanhamento">
                                        <i class="bi bi-box-arrow-up-right"></i> Visualizar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        Ainda não tem alunos ou planos atribuídos à sua supervisão.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection