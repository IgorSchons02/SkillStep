@extends('layout.app')

@section('titulo', 'Categorias')

@section('content')
    <div class="container-fluid">
        {{-- Cabeçalho --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark"><i class="bi bi-tag-fill text-primary me-2"></i>Categorias</h2>
                <p class="text-muted mb-0">Organize as tarefas do SkillStep por áreas de conhecimento.</p>
            </div>
            <button type="button" class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal"
                data-bs-target="#modalNovaCategoria">
                <i class="bi bi-tag-fill me-2"></i>Nova Categoria
            </button>
        </div>

        {{-- Filtro Real-time --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('categorias.index') }}" method="GET" id="searchForm">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="searchInput" class="form-control bg-light border-0"
                            placeholder="Buscar categoria..." value="{{ request('search') }}">
                    </div>
                </form>
            </div>
        </div>

        {{-- Grid de Cards --}}
        <div class="row g-4">
            @forelse ($categorias as $cat)
                <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 category-card"
                        style="border-left: 5px solid {{ $cat->cor_hex }} !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-bold text-dark mb-0" title="{{ $cat->nome }}">
                                    {{ \Illuminate\Support\Str::limit($cat->nome, 20) }}
                                </h5>
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                        <li>
                                            <button class="dropdown-item py-2" data-bs-toggle="modal"
                                                data-bs-target="#modalEditarCategoria"
                                                data-url="{{ route('categorias.update', $cat->id) }}"
                                                data-nome="{{ $cat->nome }}" data-descricao="{{ $cat->descricao }}"
                                                data-cor="{{ $cat->cor_hex }}">
                                                <i class="bi bi-pencil me-2 text-primary"></i>Editar
                                            </button>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <form action="{{ route('categorias.destroy', $cat->id) }}" method="POST"
                                                class="form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="dropdown-item py-2 text-danger btn-delete-category">
                                                    <i class="bi bi-trash me-2"></i>Excluir
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <p class="text-muted small mb-0 text-truncate-2">
                                {{ $cat->descricao ? \Illuminate\Support\Str::limit($cat->descricao, 100) : 'Sem descrição.' }}
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0 pb-3 ps-3">
                            <span class="badge rounded-pill bg-light text-dark border">
                                <i class="bi bi-journal-bookmark me-1" style="color: {{ $cat->cor_hex }}"></i>
                                {{ $cat->tarefas_count ?? 0 }} Tarefas
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Nenhuma categoria encontrada.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modal Nova Categoria --}}
    <div class="modal fade" id="modalNovaCategoria" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Nova Categoria</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('categorias.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome<span
                                        class="text-danger"> *</span></label></label>
                            <input type="text" name="nome" class="form-control" required maxlength="50"
                                placeholder="Ex: Segurança">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="2" maxlength="150"
                                placeholder="Informe uma breve descricao da categoria..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cor</label>
                            <input type="color" name="cor_hex" class="form-control form-control-color w-100"
                                value="#e85d2f">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary w-100">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Editar Categoria --}}
    <div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i> Editar Categoria</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarCategoria" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome</label>
                            {{-- ID ADICIONADO AQUI --}}
                            <input type="text" id="edit_cat_nome" name="nome" class="form-control" required maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descrição</label>
                            <textarea id="edit_cat_descricao" name="descricao" class="form-control" rows="2"
                                maxlength="150"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cor</label>
                            <input type="color" id="edit_cat_cor" name="cor_hex"
                                class="form-control form-control-color w-100">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary w-100">Atualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Pesquisa Real-time
            const searchInput = document.getElementById('searchInput');
            const searchForm = document.getElementById('searchForm');
            let timeout = null;

            searchInput.addEventListener('keyup', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => searchForm.submit(), 500);
            });

            // Edição via Modal
            const modalEdit = document.getElementById('modalEditarCategoria');
            if (modalEdit) {
                modalEdit.addEventListener('show.bs.modal', function (event) {
                    const btn = event.relatedTarget;

                    // Sincronizando com os IDs corretos
                    document.getElementById('formEditarCategoria').action = btn.getAttribute('data-url');
                    document.getElementById('edit_cat_nome').value = btn.getAttribute('data-nome');
                    document.getElementById('edit_cat_descricao').value = btn.getAttribute('data-descricao');
                    document.getElementById('edit_cat_cor').value = btn.getAttribute('data-cor');
                });
            }
            // --- SweetAlert2: Confirmação de Exclusão ---
            const deleteButtons = document.querySelectorAll('.btn-delete-category');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    // Pegamos o nome da categoria que está no h5 do mesmo card
                    const card = this.closest('.category-card');
                    const categoriaNome = card.querySelector('h5').innerText.trim();
                    const form = this.closest('.form-delete');

                    Swal.fire({
                        title: 'Excluir Categoria?',    
                        html: `Você está prestes a remover a categoria <strong>"${categoriaNome}"</strong>.`,//<br><small class="text-muted">Isso pode afetar tarefas vinculadas!</small>
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e85d2f',
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
    });
    </script>
@endpush