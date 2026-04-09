@extends('layout.app')

@section('titulo', 'Painel de Administração')

@section('content')
<div class="container-fluid py-4">
    
    {{-- Banner de Boas-vindas ADMIN --}}
    <div class="card border-0 rounded-4 shadow-sm mb-4 bg-dark text-white overflow-hidden relative">
        <div class="card-body p-4 p-md-5 position-relative z-1 d-flex justify-content-between align-items-center">
            <div>
                {{-- Pega apenas o primeiro nome do usuário para uma saudação mais amigável --}}
                <h2 class="fw-bold mb-2">Olá, {{ explode(' ', Auth::user()->nome)[0] }}!</h2>
                <p class="fs-5 text-light opacity-75 mb-0">Gerencie usuários, inventário de conteúdo e o progresso geral da empresa.</p>
            </div>
            {{-- Ícone do foguete com a cor laranja em destaque --}}
            <div class="d-none d-md-block">
                <i class="bi bi-rocket-takeoff-fill" style="font-size: 5rem; color: #d35400;"></i>
            </div>
        </div>
    </div>

    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-mortarboard text-primary me-2"></i>Desempenho de Aprendizado</h5>
    {{-- Row 1: Cards de Métricas de Aprendizado (Iguais ao Supervisor, mas globais) --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm card-hover h-100 border-start border-primary border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-dash bg-primary-subtle text-primary me-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted fw-bold text-uppercase small d-block">Total de Alunos</span>
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
                        <span class="text-muted fw-bold text-uppercase small d-block">Planos Atribuídos</span>
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
                        <span class="text-muted fw-bold text-uppercase small d-block">Planos Concluídos</span>
                        <h3 class="fw-bold text-dark mb-0">{{ $planosConcluidos }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h5 class="fw-bold text-dark mb-3 mt-5"><i class="bi bi-box-seam text-secondary me-2"></i>Inventário da Plataforma</h5>
    {{-- Row 2: Cards de Inventário Exclusivos do Admin --}}
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm card-hover h-100 border-start border-secondary border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-dash bg-secondary-subtle text-secondary me-3">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div>
                        <span class="text-muted fw-bold text-uppercase small d-block">Supervisores</span>
                        <h3 class="fw-bold text-dark mb-0">{{ $totalSupervisores }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm card-hover h-100 border-start border-danger border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-dash bg-danger-subtle text-danger me-3">
                        <i class="bi bi-signpost-split-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted fw-bold text-uppercase small d-block">Trilhas Base</span>
                        <h3 class="fw-bold text-dark mb-0">{{ $totalTrilhas }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm card-hover h-100 border-start border-purple border-4" style="border-left-color: #6f42c1 !important;">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-dash me-3" style="background-color: #e0d4f5; color: #6f42c1;">
                        <i class="bi bi-journal-check"></i>
                    </div>
                    <div>
                        <span class="text-muted fw-bold text-uppercase small d-block">Treinamentos</span>
                        <h3 class="fw-bold text-dark mb-0">{{ $totalTreinamentos }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm card-hover h-100 border-start border-dark border-4">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-dash bg-dark-subtle text-dark me-3">
                        <i class="bi bi-list-task"></i>
                    </div>
                    <div>
                        <span class="text-muted fw-bold text-uppercase small d-block">Tarefas Criadas</span>
                        <h3 class="fw-bold text-dark mb-0">{{ $totalTarefas }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de Acompanhamento Recente (Global) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-activity text-primary me-2"></i>Últimos Planos Atribuídos</h5>
            <a href="{{ route('planos.index') }}" class="btn btn-sm btn-outline-secondary">Gerenciar Planos</a>
        </div>
        <div class="card-body p-0">
            {{-- Adicionado max-height e overflow para a rolagem --}}
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0">
                    {{-- Cabeçalho "pegajoso" para não sumir no scroll --}}
                    <thead class="table-light text-muted small text-uppercase position-sticky top-0" style="z-index: 10;">
                        <tr>
                            <th class="ps-4 py-3 border-bottom-0 shadow-sm" style="width: 25%">Aluno</th>
                            <th class="py-3 border-bottom-0 shadow-sm" style="width: 30%">Plano de Estudos</th>
                            <th class="py-3 pe-4 border-bottom-0 shadow-sm" style="width: 20%">Progresso</th>
                            <th class="ps-5 pe-4 py-3 border-bottom-0 shadow-sm" style="width: 25%">Supervisores</th>
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
                                    <div class="text-muted small"><i class="bi bi-calendar-plus me-1"></i>Atribuído em {{ $plano->created_at->format('d/m/Y') }}</div>
                                </td>
                                <td class="pe-4">
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
                                <td class="ps-5 pe-4">
                                    @php
                                        $nomesSupervisores = 'Nenhum supervisor';
                                        if (!empty($plano->supervisores_ids)) {
                                            $ids = is_array($plano->supervisores_ids) ? $plano->supervisores_ids : json_decode($plano->supervisores_ids, true);
                                            if (is_array($ids) && count($ids) > 0) {
                                                $nomesSupervisores = \App\Models\Usuario::whereIn('id', $ids)->pluck('nome')->join(', ');
                                            }
                                        }
                                    @endphp
                                    <div class="text-muted small text-truncate" style="max-width: 220px;" title="{{ $nomesSupervisores }}">
                                        <i class="bi bi-person-badge me-2"></i> {{ $nomesSupervisores }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        Ainda não há nenhum plano de estudos criado no sistema.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- Rodapé com a Paginação do Laravel --}}
        @if ($planosAcompanhamento->hasPages())
            <div class="card-footer bg-white border-top px-4 pt-3 pb-1">
                {{ $planosAcompanhamento->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection