@extends('layout.gestor')

@section('titulo', 'Tarefas')
@section('conteudo')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Gestão de Tarefas</h2>
                <p class="text-muted">Gerencie os modelos de tarefas para os processos de onboarding.</p>
            </div>
            {{-- <a href="{{ route('tarefas.create') }}" class="btn btn-primary px-4"> --}}
                <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#modalNovaTarefa">
                    <i class="bi bi-plus-lg me-2"></i>Nova Tarefa
                </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <form action="{{ route('tarefas.index') }}" method="GET" class="row g-2">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-0"
                                placeholder="Pesquisar por título ou descrição..." value="{{ request('search') }}">
                        </div>
                    </div>
                    @if(session('codigo_area') == null)
                        <div class="col-md-4">
                            <select name="area" class="form-select bg-light border-0">
                                <option value="">Todas as Áreas</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ request('area') == $area->id ? 'selected' : '' }}>
                                        {{ $area->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-secondary w-100">Filtrar</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">
                                @php
                                    $isSortTitulo = request('sort_by') == 'titulo';
                                    $dirTitulo = $isSortTitulo && request('sort_direction') == 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'titulo', 'sort_direction' => $dirTitulo]) }}"
                                    class="text-dark text-decoration-none d-flex align-items-center">
                                    Título
                                    @if($isSortTitulo)
                                        <i
                                            class="bi bi-sort-alpha-{{ request('sort_direction') == 'asc' ? 'down' : 'up' }} ms-1 text-primary"></i>
                                    @else
                                        <i class="bi bi-arrow-down-up ms-1 text-muted"
                                            style="font-size: 0.8em; opacity: 0.5;"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Área Responsável</th>
                            <th class="d-none d-md-table-cell">
                                @php
                                    // Como data é o padrão, consideramos ele ativo se não houver sort_by na URL
                                    $isSortData = request('sort_by') == 'created_at' || !request()->has('sort_by');
                                    $currentDirData = request('sort_direction', 'asc');
                                    $dirData = $isSortData && $currentDirData == 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_direction' => $dirData]) }}"
                                    class="text-dark text-decoration-none d-flex align-items-center">
                                    Criado em
                                    @if($isSortData)
                                        <i
                                            class="bi bi-sort-numeric-{{ $currentDirData == 'asc' ? 'down' : 'up' }} ms-1 text-primary"></i>
                                    @else
                                        <i class="bi bi-arrow-down-up ms-1 text-muted"
                                            style="font-size: 0.8em; opacity: 0.5;"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($tarefas->total() > 0)
                            @foreach($tarefas as $tarefa)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $tarefa->titulo }}</div>
                                        <small class="text-muted text-truncate d-block" style="max-width: 300px;">
                                            {{ $tarefa->descricao }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info px-3 py-2">
                                            {{ $tarefa->area->name ?? 'Sem Área' }}
                                        </span>
                                    </td>
                                    <td class="d-none d-md-table-cell text-muted">
                                        {{ \Carbon\Carbon::parse($tarefa->data_criacao)->format('d/m/Y') }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                                data-bs-target="#modalVisualizarTarefa" data-titulo="{{ $tarefa->titulo }}"
                                                data-descricao="{{ $tarefa->descricao }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#modalEditarTarefa" data-id="{{ $tarefa->id }}"
                                                data-titulo="{{ $tarefa->titulo }}" data-descricao="{{ $tarefa->descricao }}"
                                                data-area="{{ $tarefa->codigo_area }}"
                                                data-url="{{ route('tarefas.update', $tarefa->id) }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            {{-- <form action="{{ route('tarefas.destroy', $tarefa->id) }}" method="POST"
                                                class="d-inline"></form>--}}
                                            {{-- Botão Excluir (Lixeira) --}}
                                            <form action="{{ route('tarefas.destroy', $tarefa->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Tem certeza que deseja excluir a tarefa: {{ $tarefa->titulo }}?\nEsta ação não poderá ser desfeita.')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-x display-4"></i>
                                    <p class="mt-2">Nenhuma tarefa encontrada.</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white py-3">
                {{ $tarefas->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
    {{-- Modal de Visualização da Tarefa --}}
    <div class="modal fade" id="modalVisualizarTarefa" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalLabel">
                        <i class="bi bi-card-text me-2 text-primary"></i>Detalhes da Tarefa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <h4 id="modalTarefaTitulo" class="fw-bold text-dark mb-3"></h4>
                    <div class="bg-light p-3 rounded">
                        <p id="modalTarefaDescricao" class="text-muted mb-0 text-break" style="white-space: pre-wrap;"></p>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Modal de Nova Tarefa --}}
    <div class="modal fade" id="modalNovaTarefa" tabindex="-1" aria-labelledby="modalNovaTarefaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalNovaTarefaLabel">
                        <i class="bi bi-plus-circle me-2 text-primary"></i>Cadastrar Nova Tarefa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Formulário apontando para a rota de Store do Laravel --}}
                <form action="{{ route('tarefas.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="titulo" class="form-label fw-bold">Título da Tarefa <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required
                                placeholder="Ex: Configurar ambiente de desenvolvimento">
                        </div>

                        <div class="mb-4">
                            <label for="descricao" class="form-label fw-bold">Descrição <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="4" required
                                placeholder="Descreva os passos detalhados ou cole links de manuais úteis aqui..."></textarea>
                        </div>

                        {{-- Regra de Negócio: Exibe o combo apenas para Super Admin (RH) --}}
                        @if(session('codigo_area') == null)
                            <div class="mb-3">
                                <label for="codigo_area" class="form-label fw-bold">Área Responsável <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="codigo_area" name="codigo_area" required>
                                    <option value="">Selecione a área para esta tarefa...</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            {{-- Se for um Gestor de Área, envia o código da área dele de forma oculta --}}
                            <input type="hidden" name="codigo_area" value="{{ session('codigo_area') }}">
                        @endif
                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Salvar Tarefa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal de Editar Tarefa --}}
    <div class="modal fade" id="modalEditarTarefa" tabindex="-1" aria-labelledby="modalEditarTarefaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalEditarTarefaLabel">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Editar Tarefa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- O action será preenchido dinamicamente pelo JS --}}
                <form id="formEditarTarefa" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="edit_titulo" class="form-label fw-bold">Título da Tarefa <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_titulo" name="titulo" required>
                        </div>

                        <div class="mb-4">
                            <label for="edit_descricao" class="form-label fw-bold">Descrição <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_descricao" name="descricao" rows="4"
                                required></textarea>
                        </div>

                        {{-- Regra do Multi-tenant igual ao cadastro --}}
                        @if(session('codigo_area') == null)
                            <div class="mb-3">
                                <label for="edit_codigo_area" class="form-label fw-bold">Área Responsável <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="edit_codigo_area" name="codigo_area" required>
                                    <option value="">Selecione a área...</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" id="edit_codigo_area" name="codigo_area" value="{{ session('codigo_area') }}">
                        @endif
                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Script para popular o Modal dinamicamente --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalVisualizar = document.getElementById('modalVisualizarTarefa');

            modalVisualizar.addEventListener('show.bs.modal', function (event) {
                // Botão que acionou o modal
                var button = event.relatedTarget;

                // Extrai as informações dos atributos data-*
                var titulo = button.getAttribute('data-titulo');
                var descricao = button.getAttribute('data-descricao');

                // Atualiza o conteúdo do modal
                modalVisualizar.querySelector('#modalTarefaTitulo').textContent = titulo;
                modalVisualizar.querySelector('#modalTarefaDescricao').textContent = descricao;
            });
        });
    </script>
    {{-- Script para popular o Modal dinamicamente e converter links --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalVisualizar = document.getElementById('modalVisualizarTarefa');

            modalVisualizar.addEventListener('show.bs.modal', function (event) {
                // Botão que acionou o modal
                var button = event.relatedTarget;

                // Extrai as informações dos atributos data-*
                var titulo = button.getAttribute('data-titulo');
                var descricaoRaw = button.getAttribute('data-descricao');

                // 1. Proteção de Segurança: Escapa o texto original para evitar XSS
                var divSeguranca = document.createElement('div');
                divSeguranca.innerText = descricaoRaw;
                var descricaoSegura = divSeguranca.innerHTML;

                // 2. Regex que encontra URLs e transforma em links clicáveis
                var urlRegex = /(https?:\/\/[^\s]+)/g;
                var descricaoFormatada = descricaoSegura.replace(urlRegex, function (url) {
                    return '<a href="' + url + '" target="_blank" class="text-primary text-decoration-underline text-break">' + url + '</a>';
                });

                // 3. Atualiza o conteúdo do modal
                modalVisualizar.querySelector('#modalTarefaTitulo').textContent = titulo;

                // Usamos innerHTML aqui para que a tag <a> seja renderizada como HTML
                modalVisualizar.querySelector('#modalTarefaDescricao').innerHTML = descricaoFormatada;
            });
        });
    </script>
    {{-- Script para popular o Modal de Edição --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalEditar = document.getElementById('modalEditarTarefa');

            if (modalEditar) {
                modalEditar.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;

                    // Extrai os dados do botão
                    var titulo = button.getAttribute('data-titulo');
                    var descricao = button.getAttribute('data-descricao');
                    var area = button.getAttribute('data-area');
                    var actionUrl = button.getAttribute('data-url');

                    // Preenche o formulário
                    modalEditar.querySelector('#edit_titulo').value = titulo;
                    modalEditar.querySelector('#edit_descricao').value = descricao;

                    // Se o campo de área existir (RH/Super Admin), seleciona a área correta
                    var selectArea = modalEditar.querySelector('#edit_codigo_area');
                    if (selectArea) {
                        selectArea.value = area;
                    }

                    // Atualiza a URL para onde o formulário vai ser enviado
                    modalEditar.querySelector('#formEditarTarefa').action = actionUrl;
                });
            }
        });
    </script>
@endsection