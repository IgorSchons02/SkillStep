@extends('layout.gestor')

@section('titulo', 'Treinamentos')

@section('conteudo')
    <div class="container-fluid">
        {{-- Cabeçalho da Página --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Gestão de Treinamentos</h2>
                <p class="text-muted">Crie e gerencie as trilhas de capacitação para o onboarding.</p>
            </div>
            <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal"
                data-bs-target="#modalNovoTreinamento">
                <i class="bi bi-plus-lg me-2"></i>Novo Treinamento
            </button>
        </div>

        {{-- Área de Filtros --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <form action="{{ route('treinamentos.index') }}" method="GET" class="row g-2">

                    {{-- A barra de pesquisa ajusta o tamanho dependendo se o filtro de área está visível --}}
                    <div class="{{ session('codigo_area') == null ? 'col-md-4' : 'col-md-7' }}">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-0"
                                placeholder="Pesquisar por nome ou descrição..." value="{{ request('search') }}">
                        </div>
                    </div>

                    @if(session('codigo_area') == null)
                        <div class="col-md-3">
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

                    <div class="col-md-3">
                        <select name="status" class="form-select bg-light border-0">
                            <option value="">Todos os Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Ativos</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inativos</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-secondary w-100">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Listagem em Cards --}}
        @if($treinamentos->total() > 0)
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
                @foreach($treinamentos as $treinamento)
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body">
                                {{-- Status e Área no topo do Card --}}
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-info-subtle text-info px-2 py-1">
                                        {{ $treinamento->area->name ?? 'Sem Área' }}
                                    </span>
                                    @if($treinamento->ativo)
                                        <span class="badge bg-success-subtle text-success"><i
                                                class="bi bi-check-circle me-1"></i>Ativo</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary"><i
                                                class="bi bi-dash-circle me-1"></i>Inativo</span>
                                    @endif
                                </div>

                                {{-- Título --}}
                                <h5 class="card-title fw-bold text-dark mt-3 mb-1 text-truncate" title="{{ $treinamento->nome }}">
                                    {{ $treinamento->nome }}
                                </h5>
                                <p class="card-text text-muted small mt-2 mb-0"
                                    style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; min-height: 40px;"
                                    title="{{ $treinamento->descricao }}">
                                    {{ $treinamento->descricao ?: 'Nenhuma descrição informada.' }}
                                </p>

                                {{-- Informações Adicionais --}}
                                <div class="mt-3 text-muted small">
                                    <div class="mb-2 d-flex align-items-center">
                                        <i class="bi bi-list-task me-2 fs-5"></i>
                                        <strong>{{ $treinamento->tarefas_count ?? $treinamento->tarefas()->count() }}</strong>
                                        &nbsp;tarefas vinculadas
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar3 me-2"></i>
                                        Criado em {{ \Carbon\Carbon::parse($treinamento->created_at)->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>

                            {{-- Footer do Card com os Botões --}}
                            <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                                {{-- Botão Visualizar na Esquerda --}}
                                <button type="button" class="btn btn-sm btn-outline-info px-3" data-bs-toggle="modal"
                                    data-bs-target="#modalVisualizarTreinamento" data-nome="{{ $treinamento->nome }}"
                                    data-descricao="{{ $treinamento->descricao }}"
                                    data-ativo="{{ $treinamento->ativo ? '1' : '0' }}"
                                    data-area-nome="{{ $treinamento->area->name ?? 'Sem Área' }}"
                                    data-tarefas="{{ $treinamento->tarefas->pluck('id')->toJson() }}">
                                    <i class="bi bi-eye me-1"></i> Visualizar
                                </button>

                                {{-- Botões de Ação na Direita --}}
                                <div class="btn-group">
                                    {{-- Botão Editar (Lápis) --}}
                                    @php
                                        // Pega os IDs das tarefas deste treinamento na ordem correta
                                        $tarefasIds = $treinamento->tarefas->pluck('id')->toJson();
                                    @endphp
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#modalEditarTreinamento" data-id="{{ $treinamento->id }}"
                                        data-nome="{{ $treinamento->nome }}" data-descricao="{{ $treinamento->descricao }}"
                                        data-ativo="{{ $treinamento->ativo ? '1' : '0' }}"
                                        data-area="{{ $treinamento->codigo_area }}" data-tarefas="{{ $tarefasIds }}"
                                        data-url="{{ route('treinamentos.update', $treinamento->id) }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <form action="{{ route('treinamentos.destroy', $treinamento->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Tem certeza que deseja excluir o treinamento: {{ $treinamento->nome }}?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Paginação --}}
            <div class="d-flex justify-content-center">
                {{ $treinamentos->appends(request()->query())->links() }}
            </div>
        @else
            {{-- Estado Vazio --}}
            <div class="card shadow-sm border-0 p-5 text-center">
                <div class="text-muted">
                    <i class="bi bi-journal-x display-4"></i>
                    <h4 class="mt-3">Nenhum treinamento encontrado</h4>
                    <p>Crie uma nova trilha de capacitação para começar.</p>
                </div>
            </div>
        @endif
    </div>
    {{-- Modal de Visualizar Treinamento --}}
    <div class="modal fade" id="modalVisualizarTreinamento" tabindex="-1" aria-labelledby="modalVisualizarTreinamentoLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalVisualizarTreinamentoLabel">
                        <i class="bi bi-journal-text me-2 text-info"></i>Detalhes do Treinamento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    {{-- Cabeçalho com Badges --}}
                    <div class="d-flex align-items-center mb-3 gap-2" id="view_badges">
                        <span class="badge bg-info-subtle text-info fs-6 px-3 py-2" id="view_area"></span>
                        <span class="badge fs-6 px-3 py-2" id="view_status"></span>
                    </div>

                    {{-- Título e Descrição --}}
                    <h3 id="view_nome" class="fw-bold text-dark mb-3"></h3>

                    <div class="bg-light p-3 rounded mb-4">
                        <p id="view_descricao" class="text-muted mb-0 text-break" style="white-space: pre-wrap;"></p>
                    </div>

                    {{-- Lista de Tarefas Vinculadas --}}
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Trilha de Tarefas</h5>

                    <div class="border rounded bg-white p-2">
                        <ol class="list-group list-group-numbered list-group-flush" id="view_lista_tarefas">
                        </ol>
                    </div>
                </div>

                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Modal de Novo Treinamento --}}
    <div class="modal fade" id="modalNovoTreinamento" tabindex="-1" aria-labelledby="modalNovoTreinamentoLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalNovoTreinamentoLabel">
                        <i class="bi bi-plus-circle me-2 text-primary"></i>Criar Novo Treinamento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Formulário apontando para a rota de Store --}}
                <form action="{{ route('treinamentos.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">

                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <label for="nome" class="form-label fw-bold">Nome do Treinamento <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome" name="nome" required
                                    placeholder="Ex: Trilha de Onboarding - Desenvolvedores">
                            </div>

                            {{-- Switch de Status (Ativo/Inativo) --}}
                            <div class="col-md-3 mb-3 d-flex align-items-center justify-content-end mt-md-4">
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo" checked>
                                    <label class="form-check-label fs-6 ms-2" for="ativo">Ativo</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="descricao" class="form-label fw-bold">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="4"
                                placeholder="Descreva o objetivo geral desta trilha de capacitação..."></textarea>
                        </div>
                        {{-- Área de Seleção de Tarefas (Padrão Dual Listbox) --}}
                        <div class="mb-4 border-top pt-4">
                            <label class="form-label fw-bold">Tarefas do Treinamento
                                <!-- <span class="text-danger">*</span></label> -->
                                <p class="text-muted small mb-3">Pesquise e adicione as tarefas. A ordem na lista da direita
                                    define a sequência do treinamento.</p>

                                <div class="row g-3">
                                    {{-- Lado Esquerdo: Pesquisa e Seleção --}}
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-secondary">Tarefas Disponíveis</label>

                                        {{-- Campo de Pesquisa --}}
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                            <input type="text" id="pesquisaTarefa" class="form-control"
                                                placeholder="Pesquisar pelo título...">
                                        </div>

                                        {{-- Combo Box visível (size="6" faz ela ficar aberta) --}}
                                        <select id="selectTarefasDisponiveis" class="form-select form-select-sm" size="6"
                                            style="height: 160px;">
                                            @foreach($tarefasDisponiveis as $tarefa)
                                                <option value="{{ $tarefa->id }}">
                                                    {{ $tarefa->titulo }}
                                                    @if(session('codigo_area') == null)
                                                        ({{ $tarefa->area->name ?? 'Sem Área' }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="button" id="btnAdicionarTarefa"
                                            class="btn btn-sm btn-outline-primary w-100 mt-2">
                                            Adicionar Tarefa <i class="bi bi-arrow-right ms-1"></i>
                                        </button>
                                    </div>

                                    {{-- Lado Direito: Tarefas Selecionadas (O que vai pro banco) --}}
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-secondary">Tarefas Selecionadas</label>

                                        <div class="border rounded bg-light p-2" style="height: 205px; overflow-y: auto;">
                                            <ul class="list-group list-group-flush" id="listaTarefasSelecionadas">
                                                {{-- Estado Vazio inicial --}}
                                                <li class="list-group-item text-muted text-center small bg-transparent border-0"
                                                    id="emptyStateTarefas">
                                                    Nenhuma tarefa adicionada ainda.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        {{-- Regra de Negócio: Exibe o combo apenas para Super Admin (RH) --}}
                        @if(session('codigo_area') == null)
                            <div class="mb-3 border-top pt-3">
                                <label for="codigo_area" class="form-label fw-bold">Área Responsável <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="codigo_area" name="codigo_area" required>
                                    <option value="">Selecione a área deste treinamento...</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Apenas colaboradores desta área terão acesso a este
                                    treinamento.</small>
                            </div>
                        @else
                            {{-- Se for um Gestor de Área, envia o código da área dele de forma oculta --}}
                            <input type="hidden" name="codigo_area" value="{{ session('codigo_area') }}">
                        @endif

                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Salvar Treinamento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal de Edição de Treinamento --}}
    <div class="modal fade" id="modalEditarTreinamento" tabindex="-1" aria-labelledby="modalEditarTreinamentoLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalEditarTreinamentoLabel">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Editar Treinamento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formEditarTreinamento" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">

                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <label for="edit_nome" class="form-label fw-bold">Nome do Treinamento <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_nome" name="nome" required>
                            </div>

                            <div class="col-md-3 mb-3 d-flex align-items-center justify-content-end mt-md-4">
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" id="edit_ativo" name="ativo">
                                    <label class="form-check-label fs-6 ms-2" for="edit_ativo">Ativo</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="edit_descricao" class="form-label fw-bold">Descrição</label>
                            <textarea class="form-control" id="edit_descricao" name="descricao" rows="4"></textarea>
                        </div>

                        @if(session('codigo_area') == null)
                            <div class="mb-3 border-top pt-3">
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

                        {{-- Área de Seleção de Tarefas (Edição) --}}
                        <div class="mb-4 border-top pt-4">
                            <label class="form-label fw-bold">Tarefas do Treinamento
                                <!-- <span class="text-danger">*</span></label> -->
                                <p class="text-muted small mb-3">Pesquise e adicione as tarefas. A ordem na lista da direita
                                    define a sequência do treinamento.</p>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-secondary">Tarefas Disponíveis</label>
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                            <input type="text" id="edit_pesquisaTarefa" class="form-control"
                                                placeholder="Pesquisar...">
                                        </div>
                                        <select id="edit_selectTarefasDisponiveis" class="form-select form-select-sm"
                                            size="6" style="height: 160px;">
                                        </select>
                                        <button type="button" id="edit_btnAdicionarTarefa"
                                            class="btn btn-sm btn-outline-primary w-100 mt-2">
                                            Adicionar Tarefa <i class="bi bi-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-secondary">Tarefas Selecionadas</label>
                                        <div class="border rounded bg-light p-2" style="height: 205px; overflow-y: auto;">
                                            <ul class="list-group list-group-flush" id="edit_listaTarefasSelecionadas">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                        </div>

                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Elementos da tela
            const selectDisponiveis = document.getElementById('selectTarefasDisponiveis');
            const btnAdicionar = document.getElementById('btnAdicionarTarefa');
            const listaSelecionadas = document.getElementById('listaTarefasSelecionadas');
            const inputPesquisa = document.getElementById('pesquisaTarefa');
            const emptyState = document.getElementById('emptyStateTarefas');

            // 1. Lógica de Pesquisa (Filtro)
            inputPesquisa.addEventListener('keyup', function () {
                let filtro = this.value.toLowerCase();
                let opcoes = selectDisponiveis.options;

                for (let i = 0; i < opcoes.length; i++) {
                    let textoOpcao = opcoes[i].text.toLowerCase();
                    // Mostra ou esconde a <option> baseado no texto digitado
                    opcoes[i].style.display = textoOpcao.includes(filtro) ? '' : 'none';
                }
            });

            // 2. Lógica de Adicionar Tarefa
            btnAdicionar.addEventListener('click', function () {
                // Pega a opção que o usuário clicou na esquerda
                let opcaoSelecionada = selectDisponiveis.options[selectDisponiveis.selectedIndex];

                if (!opcaoSelecionada) {
                    alert('Selecione uma tarefa na lista primeiro.');
                    return;
                }

                let tarefaId = opcaoSelecionada.value;
                let tarefaNome = opcaoSelecionada.text;

                // Esconde o aviso de "Nenhuma tarefa"
                if (emptyState) emptyState.style.display = 'none';

                // Cria o item da lista na direita
                let li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center small py-2 px-2 border-bottom';
                li.innerHTML = `
                                                <span class="text-truncate" style="max-width: 85%;" title="${tarefaNome}">
                                                    <i class="bi bi-grip-vertical text-muted me-1"></i> ${tarefaNome}
                                                </span>
                                                <input type="hidden" name="tarefas[]" value="${tarefaId}">
                                                <button type="button" class="btn btn-sm text-danger p-0 ms-2 btn-remover-tarefa" title="Remover">
                                                    <i class="bi bi-x-circle-fill"></i>
                                                </button>
                                            `;

                // Adiciona o evento de remover no "x" recém-criado
                li.querySelector('.btn-remover-tarefa').addEventListener('click', function () {
                    // Remove o item da direita
                    li.remove();

                    // Devolve a opção para a lista da esquerda
                    opcaoSelecionada.style.display = '';
                    selectDisponiveis.appendChild(opcaoSelecionada);

                    // Mostra o "empty state" se a lista ficar vazia
                    if (listaSelecionadas.querySelectorAll('li').length === 1) { // 1 = só o empty state
                        emptyState.style.display = 'block';
                    }
                });

                // Adiciona na lista da direita
                listaSelecionadas.appendChild(li);

                // Tira da lista da esquerda (para não selecionar duplicado)
                opcaoSelecionada.style.display = 'none';
                // Desmarca a opção
                selectDisponiveis.selectedIndex = -1;
            });

            // Opcional: Permite adicionar dando um "Duplo Clique" na tarefa da esquerda
            selectDisponiveis.addEventListener('dblclick', function () {
                btnAdicionar.click();
            });
            //edicao
            // --- LÓGICA DO MODAL DE EDIÇÃO ---
            const modalEditar = document.getElementById('modalEditarTreinamento');

            // Passa todas as tarefas do Laravel para um Array no JavaScript
            const todasTarefas = @json($tarefasDisponiveis);

            if (modalEditar) {
                const editSelectDisponiveis = document.getElementById('edit_selectTarefasDisponiveis');
                const editListaSelecionadas = document.getElementById('edit_listaTarefasSelecionadas');
                const editBtnAdicionar = document.getElementById('edit_btnAdicionarTarefa');
                const editInputPesquisa = document.getElementById('edit_pesquisaTarefa');

                // CORREÇÃO: Função movida para fora do evento 'show.bs.modal'
                // Assim, o botão de Adicionar consegue enxergar ela!
                const criarItemSelecionado = (tarefa) => {
                    let li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center small py-2 px-2 border-bottom';
                    li.innerHTML = `
                                            <span class="text-truncate" style="max-width: 85%;" title="${tarefa.titulo}">
                                                <i class="bi bi-grip-vertical text-muted me-1"></i> ${tarefa.titulo}
                                            </span>
                                            <input type="hidden" name="tarefas[]" value="${tarefa.id}">
                                            <button type="button" class="btn btn-sm text-danger p-0 ms-2 btn-remover-tarefa" title="Remover">
                                                <i class="bi bi-x-circle-fill"></i>
                                            </button>
                                        `;

                    // Evento de remover
                    li.querySelector('.btn-remover-tarefa').addEventListener('click', function () {
                        li.remove();
                        // Cria a option de volta na lista da esquerda
                        let option = new Option(tarefa.titulo, tarefa.id);
                        editSelectDisponiveis.appendChild(option);
                    });

                    editListaSelecionadas.appendChild(li);
                };

                modalEditar.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;

                    // 1. Preenche os dados básicos textuais
                    modalEditar.querySelector('#formEditarTreinamento').action = button.getAttribute('data-url');
                    modalEditar.querySelector('#edit_nome').value = button.getAttribute('data-nome');
                    modalEditar.querySelector('#edit_descricao').value = button.getAttribute('data-descricao');
                    modalEditar.querySelector('#edit_ativo').checked = button.getAttribute('data-ativo') === '1';

                    const selectArea = modalEditar.querySelector('#edit_codigo_area');
                    if (selectArea) selectArea.value = button.getAttribute('data-area');

                    // 2. Lógica das Tarefas (Dual Listbox)
                    editSelectDisponiveis.innerHTML = ''; // Limpa lista esquerda
                    editListaSelecionadas.innerHTML = ''; // Limpa lista direita

                    // Converte a string JSON de IDs que veio no botão para um Array numérico (Ex: [1, 4, 2])
                    const tarefasSalvasIds = JSON.parse(button.getAttribute('data-tarefas'));

                    // A) Primeiro monta a lista da Direita (Tarefas Selecionadas) MANTENDO A ORDEM
                    tarefasSalvasIds.forEach(id => {
                        let tarefa = todasTarefas.find(t => t.id == id);
                        if (tarefa) {
                            criarItemSelecionado(tarefa);
                        }
                    });

                    // B) Depois monta a lista da Esquerda (Tarefas Disponíveis)
                    todasTarefas.forEach(tarefa => {
                        // Só adiciona na esquerda se a tarefa NÃO estiver na lista de salvas
                        if (!tarefasSalvasIds.includes(tarefa.id)) {
                            let option = new Option(tarefa.titulo, tarefa.id);
                            editSelectDisponiveis.appendChild(option);
                        }
                    });
                });

                // 3. Pesquisa na Esquerda (Edição)
                editInputPesquisa.addEventListener('keyup', function () {
                    let filtro = this.value.toLowerCase();
                    let opcoes = editSelectDisponiveis.options;
                    for (let i = 0; i < opcoes.length; i++) {
                        opcoes[i].style.display = opcoes[i].text.toLowerCase().includes(filtro) ? '' : 'none';
                    }
                });

                // 4. Mover da Esquerda para a Direita (Edição)
                editBtnAdicionar.addEventListener('click', function () {
                    let opcaoSelecionada = editSelectDisponiveis.options[editSelectDisponiveis.selectedIndex];
                    if (!opcaoSelecionada) return;

                    // Busca o objeto completo da tarefa na variável global
                    let tarefa = todasTarefas.find(t => t.id == opcaoSelecionada.value);
                    if (tarefa) {
                        criarItemSelecionado(tarefa);
                        opcaoSelecionada.remove(); // Remove da esquerda permanentemente
                    }
                });

                editSelectDisponiveis.addEventListener('dblclick', function () { editBtnAdicionar.click(); });
            }
            // --- LÓGICA DO MODAL DE VISUALIZAÇÃO ---
            const modalVisualizar = document.getElementById('modalVisualizarTreinamento');

            if (modalVisualizar) {
                modalVisualizar.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;

                    // 1. Preenche os dados textuais básicos
                    modalVisualizar.querySelector('#view_nome').textContent = button.getAttribute('data-nome');
                    modalVisualizar.querySelector('#view_area').textContent = button.getAttribute('data-area-nome');

                    // 2. Status Badge (Ativo/Inativo)
                    const statusBadge = modalVisualizar.querySelector('#view_status');
                    if (button.getAttribute('data-ativo') === '1') {
                        statusBadge.className = 'badge bg-success-subtle text-success fs-6 px-3 py-2';
                        statusBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i>Ativo';
                    } else {
                        statusBadge.className = 'badge bg-secondary-subtle text-secondary fs-6 px-3 py-2';
                        statusBadge.innerHTML = '<i class="bi bi-dash-circle me-1"></i>Inativo';
                    }

                    // 3. Lógica da Descrição (Transforma links em clicáveis)
                    const descricaoRaw = button.getAttribute('data-descricao') || 'Nenhuma descrição informada.';
                    const divSeguranca = document.createElement('div');
                    divSeguranca.innerText = descricaoRaw;
                    const descricaoSegura = divSeguranca.innerHTML;

                    // Regex para encontrar URLs e gerar links clicáveis
                    const urlRegex = /(https?:\/\/[^\s]+)/g;
                    modalVisualizar.querySelector('#view_descricao').innerHTML = descricaoSegura.replace(urlRegex, function (url) {
                        return '<a href="' + url + '" target="_blank" class="text-primary text-decoration-underline">' + url + '</a>';
                    });

                    // 4. Lógica de Listagem das Tarefas
                    const viewListaTarefas = modalVisualizar.querySelector('#view_lista_tarefas');
                    viewListaTarefas.innerHTML = ''; // Limpa a lista anterior

                    const tarefasIds = JSON.parse(button.getAttribute('data-tarefas'));

                    if (tarefasIds.length === 0) {
                        viewListaTarefas.innerHTML = '<li class="list-group-item text-muted text-center border-0 py-3">Nenhuma tarefa vinculada a este treinamento.</li>';
                    } else {
                        // Percorre os IDs na ordem correta e busca o título na variável global 'todasTarefas'
                        tarefasIds.forEach(id => {
                            let tarefa = todasTarefas.find(t => t.id == id);
                            if (tarefa) {
                                let li = document.createElement('li');
                                li.className = 'list-group-item d-flex justify-content-between align-items-start py-2';
                                li.innerHTML = `
                                            <div class="ms-2 me-auto text-truncate" title="${tarefa.titulo}">
                                                <div class="fw-bold">${tarefa.titulo}</div>
                                            </div>
                                        `;
                                viewListaTarefas.appendChild(li);
                            }
                        });
                    }
                });
            }
        });
    </script>
@endsection