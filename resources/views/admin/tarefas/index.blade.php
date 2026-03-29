@extends('layout.app')

@section('titulo', 'Gestão de Tarefas')

@section('content')
    <div class="container-fluid">
        {{-- Cabeçalho --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark">Gestão de Tarefas</h2>
                <p class="text-muted mb-0">Organize os materiais e atividades para a montagem dos treinamentos.</p>
            </div>
            <button type="button" class="btn btn-primary px-4 shadow-sm fw-bold" data-bs-toggle="modal"
                data-bs-target="#modalNovaTarefa">
                <i class="bi bi-plus-lg me-2"></i>Nova Tarefa
            </button>
        </div>

        {{-- Filtros (Real-time) --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form action="{{ route('tarefas.index') }}" method="GET" id="searchForm" class="row g-2">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" id="searchInput" class="form-control bg-light border-0"
                                placeholder="Pesquisar por título..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="categoria_id" id="filtroCategoria" class="form-select bg-light border-0">
                            <option value="">Todas as Categorias</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" id="filtroStatus" class="form-select bg-light border-0">
                            <option value="">Todos os Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Apenas Ativos</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Apenas Inativos</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabela de Tarefas --}}
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase small fw-bold text-muted">
                        <tr>
                            <th class="ps-4" style="width: 25%">Título da Tarefa</th>
                            <th style="width: 20%">Link / Conteúdo</th>
                            <th style="width: 15%">Categoria</th>
                            <th style="width: 10%">Tempo</th>
                            <th style="width: 10%">Status</th>
                            <th style="width: 10%">Criado em</th>
                            <th class="text-end pe-4" style="width: 10%">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tarefas as $tarefa)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $tarefa->titulo }}</div>
                                </td>
                                <td class="text-muted small">
                                    <div class="text-truncate-2" style="max-width: 200px;" title="{{ $tarefa->descricao }}">
                                        {!! preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1" target="_blank" class="text-primary text-decoration-underline"><i class="bi bi-link-45deg"></i> Link</a>', e($tarefa->descricao)) !!}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge"
                                        style="background-color: {{ $tarefa->categoria->cor_hex ?? '#6c757d' }}">
                                        {{ $tarefa->categoria->nome ?? 'Sem categoria' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-muted">
                                        <i class="bi bi-clock me-1"></i>{{ number_format($tarefa->tempo_estimado, 1, ',', '') }}h
                                    </span>
                                </td>
                                <td>
                                    @if($tarefa->status)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Ativo</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ \Carbon\Carbon::parse($tarefa->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal"
                                            data-bs-target="#modalEditarTarefa"
                                            data-url="{{ route('tarefas.update', $tarefa->id) }}"
                                            data-titulo="{{ $tarefa->titulo }}" data-descricao="{{ $tarefa->descricao }}"
                                            data-tempo="{{ $tarefa->tempo_estimado }}"
                                            data-categoria="{{ $tarefa->categoria_id }}" data-status="{{ $tarefa->status }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <form action="{{ route('tarefas.destroy', $tarefa->id) }}" method="POST"
                                            class="d-inline form-delete">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                                    <p class="mt-3 text-muted">Nenhuma tarefa encontrada.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Removido o d-flex e adicionado padding px-4 para o Laravel usar seu próprio justify-content-between --}}
            <div class="card-footer bg-white border-top px-4 pt-3 pb-1">
                {{ $tarefas->withQueryString()->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL NOVA TAREFA --}}
    <div class="modal fade" id="modalNovaTarefa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Nova Tarefa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('tarefas.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row mb-3">
                            <div class="col-md-9">
                                <label class="form-label fw-bold">Título da Tarefa <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="titulo" class="form-control" required maxlength="100"
                                    placeholder="Ex: Treinamento de VPN">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold d-block">Status</label>
                                <div class="form-check form-switch mt-2 fs-5">
                                    <input class="form-check-input" type="checkbox" name="status" id="statusNovo" value="1"
                                        checked>
                                    <label class="form-check-label fs-6 fw-bold ms-1" for="statusNovo"
                                        id="labelStatusNovo">Ativo</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Categoria <span class="text-danger">*</span></label>
                                <select name="categoria_id" class="form-select" required>
                                    <option value="">Selecione...</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tempo Estimado (horas) <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <input type="number" name="tempo_estimado" class="form-control" required min="0.1"
                                        step="0.1" placeholder="Ex: 1.5">
                                </div>
                                <small class="text-muted">Use ponto para decimais (Ex: 0.5 = 30min)</small>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold">Descrição / Instruções <span
                                    class="text-danger">*</span></label>
                            <textarea name="descricao" class="form-control" rows="3" required
                                placeholder="Descreva a tarefa. Você pode colar links de vídeos ou materiais aqui..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary px-4 fw-bold"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Salvar Tarefa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDITAR TAREFA --}}
    <div class="modal fade" id="modalEditarTarefa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Editar Tarefa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarTarefa" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row mb-3">
                            <div class="col-md-9">
                                <label class="form-label fw-bold">Título da Tarefa <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="edit_titulo" name="titulo" class="form-control" required
                                    maxlength="100">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold d-block">Status</label>
                                <div class="form-check form-switch mt-2 fs-5">
                                    <input type="hidden" name="status" value="0">
                                    <input class="form-check-input" type="checkbox" id="edit_status" name="status"
                                        value="1">
                                    <label class="form-check-label fs-6 fw-bold ms-1" for="edit_status"
                                        id="labelStatusEdit">Ativo</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Categoria <span class="text-danger">*</span></label>
                                <select id="edit_categoria" name="categoria_id" class="form-select" required>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tempo Estimado (horas) <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <input type="number" id="edit_tempo" name="tempo_estimado" class="form-control" required
                                        min="0.1" step="0.1">
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold">Descrição / Instruções <span
                                    class="text-danger">*</span></label>
                            <textarea id="edit_descricao" name="descricao" class="form-control" rows="3" required
                                placeholder="Descreva a tarefa..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary px-4 fw-bold"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
        }
        
        /* Ajuste fino opcional: garante que no mobile a paginação se adapte bem */
        .pagination {
            flex-wrap: wrap;
            justify-content: center;
        }
    </style>
@endpush

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // --- Labels Dinâmicos dos Switches (Ativo/Inativo) ---
            const updateStatusLabel = (checkbox, labelId) => {
                document.getElementById(labelId).innerText = checkbox.checked ? "Ativo" : "Inativo";
                document.getElementById(labelId).className = checkbox.checked ? "form-check-label fs-6 fw-bold ms-1 text-success" : "form-check-label fs-6 fw-bold ms-1 text-secondary";
            };

            const statusNovo = document.getElementById("statusNovo");
            if (statusNovo) {
                statusNovo.addEventListener("change", function () { updateStatusLabel(this, "labelStatusNovo"); });
            }

            const statusEdit = document.getElementById("edit_status");
            if (statusEdit) {
                statusEdit.addEventListener("change", function () { updateStatusLabel(this, "labelStatusEdit"); });
            }

            // --- Pesquisa e Filtro Real-time ---
            const searchForm = document.getElementById('searchForm');
            let timeout = null;

            document.getElementById('searchInput').addEventListener('keyup', function () {
                clearTimeout(timeout);
                timeout = setTimeout(() => searchForm.submit(), 500);
            });

            document.getElementById('filtroCategoria').addEventListener('change', function () {
                searchForm.submit();
            });

            document.getElementById('filtroStatus').addEventListener('change', function () {
                searchForm.submit();
            });

            // --- Preencher Modal de Edição ---
            const editButtons = document.querySelectorAll('[data-bs-target="#modalEditarTarefa"]');
            editButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    document.getElementById('formEditarTarefa').action = this.getAttribute('data-url');
                    document.getElementById('edit_titulo').value = this.getAttribute('data-titulo');
                    document.getElementById('edit_descricao').value = this.getAttribute('data-descricao');
                    document.getElementById('edit_tempo').value = this.getAttribute('data-tempo');
                    document.getElementById('edit_categoria').value = this.getAttribute('data-categoria');

                    // Switch de status
                    const statusCheckbox = document.getElementById('edit_status');
                    statusCheckbox.checked = this.getAttribute('data-status') == '1';
                    updateStatusLabel(statusCheckbox, "labelStatusEdit");
                });
            });

            // --- SweetAlert2: Confirmação de Exclusão ---
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    const form = this.closest('form');
                    const tituloTarefa = this.closest('tr').querySelector('.fw-bold').innerText;

                    Swal.fire({
                        title: 'Excluir Tarefa?',
                        html: `Deseja realmente remover <strong>"${tituloTarefa}"</strong>?<br><small class="text-danger mt-2 d-block">Atenção: Se esta tarefa estiver em algum treinamento, ela não poderá ser excluída.</small>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#1e2d40',
                        confirmButtonText: 'Sim, excluir!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // --- Reabrir Modais em Caso de Erro ---
            @if($errors->any())
                const modalNovaTarefa = document.getElementById('modalNovaTarefa');
                if (modalNovaTarefa) {
                    new bootstrap.Modal(modalNovaTarefa).show();
                }
            @endif
        });
    </script>
@endpush