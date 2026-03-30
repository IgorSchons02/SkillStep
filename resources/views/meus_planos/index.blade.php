@extends('layout.app')

@section('titulo', 'Meus Planos de Estudo')

@push('css')
<style>
    .card-meu-plano { transition: transform 0.2s, box-shadow 0.2s; border-radius: 12px; }
    .card-meu-plano:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; border-color: #e85d2f; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    {{-- Cabeçalho --}}
    <div class="mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Meus Planos de Estudo</h2>
        <p class="text-muted mb-0">Acompanhe sua jornada de aprendizado e continue de onde parou.</p>
    </div>

    {{-- Filtros (Real-time) --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form action="{{ route('meus-planos.index') }}" method="GET" id="searchForm" class="row g-2">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="pesquisaPlanoGrid" class="form-control bg-light border-0" placeholder="Pesquisar plano..." value="{{ request('search') }}" />
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" id="filtroStatus" class="form-select bg-light border-0">
                        <option value="">Todos os Status</option>
                        <option value="andamento" {{ request('status') === 'andamento' ? 'selected' : '' }}>Em Andamento</option>
                        <option value="concluido" {{ request('status') === 'concluido' ? 'selected' : '' }}>Concluídos</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- Grid de Planos --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
        @forelse ($planos as $plano)
            <div class="col">
                <div class="card h-100 shadow-sm card-meu-plano border-0 border-top border-4 {{ $plano->progresso == 100 ? 'border-success' : 'border-primary' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="badge {{ $plano->progresso == 100 ? 'bg-success' : 'bg-primary-subtle text-primary border border-primary-subtle px-3 py-2' }}">
                                {{ $plano->progresso == 100 ? 'Concluído' : 'Em Andamento' }}
                            </span>
                            <small class="text-muted" title="Data de Atribuição"><i class="bi bi-calendar-event me-1"></i>{{ $plano->created_at->format('d/m/Y') }}</small>
                        </div>
                        
                        <h5 class="fw-bold mb-3 text-dark">{{ $plano->titulo }}</h5>
                        
                        <div class="d-flex justify-content-between small mb-1 mt-4">
                            <span class="text-muted fw-bold">Seu Progresso</span>
                            <span class="fw-bold {{ $plano->progresso == 100 ? 'text-success' : 'text-primary' }}">{{ $plano->progresso }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $plano->progresso == 100 ? 'bg-success' : 'bg-primary' }} progress-bar-striped {{ $plano->progresso < 100 ? 'progress-bar-animated' : '' }}" style="width: {{ $plano->progresso }}%"></div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent border-0 pb-3 pt-0">
                        <a href="{{ route('meus-planos.show', $plano->id) }}" class="btn btn-outline-primary fw-bold w-100">
                            Ver Detalhes <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 py-5 text-center">
                <div class="p-5 bg-white rounded shadow-sm border">
                    <i class="bi bi-journal-x text-muted mb-3" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold text-dark">Nenhum plano encontrado</h5>
                    <p class="text-muted">Você ainda não possui planos de estudo atribuídos ou nenhum corresponde à sua busca.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $planos->withQueryString()->links() }}
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Busca dinâmica em tempo real
        const searchForm = document.getElementById('searchForm');
        let timeout = null;

        document.getElementById('pesquisaPlanoGrid').addEventListener('keyup', function () {
            clearTimeout(timeout);
            timeout = setTimeout(() => searchForm.submit(), 500);
        });

        document.getElementById('filtroStatus').addEventListener('change', function () {
            searchForm.submit();
        });
    });
</script>
@endpush