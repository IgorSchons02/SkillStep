@extends('layout.app')

@section('titulo', 'Gestão de Treinamentos')

@section('content')
    <div class="container-fluid">
        {{-- Cabeçalho --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Treinamentos</h2>
                <p class="text-muted mb-0">Monte treinamentos associando tarefas ativas em uma jornada sequencial.</p>
            </div>
            <button type="button" class="btn btn-primary px-4 shadow-sm fw-bold" data-bs-toggle="modal"
                data-bs-target="#modalTreinamento" onclick="prepararNovoTreinamento()">
                <i class="bi bi-plus-lg me-2"></i>Novo Treinamento
            </button>
        </div>

        {{-- Filtros (Real-time) --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form action="{{ route('treinamentos.index') }}" method="GET" id="searchForm" class="row g-2">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" id="searchInput" class="form-control bg-light border-0"
                                placeholder="Pesquisar treinamentos..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <select name="status" id="filtroStatus" class="form-select bg-light border-0">
                            <option value="">Todos os Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Ativos</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inativos</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        {{-- Grid de Treinamentos --}}
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
            @forelse($treinamentos as $treinamento)
                @php
                    // Filtramos apenas as tarefas ativas para não quebrar os totalizadores da Grid
                    $tarefasAtivas = $treinamento->tarefas->where('status', 1)->values();

                    // Cálculo do tempo em horas e minutos
                    $tempoDecimalGrid = $tarefasAtivas->sum('tempo_estimado');
                    $horasGrid = floor($tempoDecimalGrid);
                    $minutosGrid = round(($tempoDecimalGrid - $horasGrid) * 60);
                    $tempoFormatadoGrid = '';

                    if ($horasGrid > 0 && $minutosGrid > 0) {
                        $tempoFormatadoGrid = "{$horasGrid}h {$minutosGrid}m";
                    } elseif ($horasGrid > 0) {
                        $tempoFormatadoGrid = "{$horasGrid}h";
                    } else {
                        $tempoFormatadoGrid = "{$minutosGrid}m";
                    }
                @endphp
                <div class="col">
                    <div class="card h-100 shadow-sm border card-treinamento">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <div class="d-flex gap-1">
                                    <span class="badge bg-primary">{{ $tarefasAtivas->count() }} Tarefas</span>
                                    <span class="badge bg-info text-dark"><i class="bi bi-clock"></i>
                                        {{ $tempoFormatadoGrid }}</span>
                                </div>
                                <span
                                    class="badge {{ $treinamento->status ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} border">
                                    {{ $treinamento->status ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                            <h5 class="fw-bold mb-2">{{ $treinamento->nome }}</h5>
                            <p class="text-muted small mb-0 text-truncate" style="max-height: 40px;">
                                {{ $treinamento->descricao ?? 'Sem descrição informada.' }}
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3 d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-sm btn-light border px-3 text-primary"
                                onclick="visualizarTreinamento({{ $treinamento->id }})" title="Visualizar a jornada">
                                <i class="bi bi-eye"></i> Visualizar
                            </button>

                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary me-2"
                                    onclick="editarTreinamento({{ $treinamento->id }}, '{{ $treinamento->nome }}', '{{ $treinamento->descricao }}', {{ $treinamento->status }}, {{ json_encode($tarefasAtivas->pluck('id')) }}, {{ isset($treinamento->em_trilha) && $treinamento->em_trilha ? 'true' : 'false' }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('treinamentos.destroy', $treinamento->id) }}" method="POST"
                                    class="d-inline form-delete">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5 bg-white rounded border shadow-sm">
                        <i class="bi bi-journal-x text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3 text-muted">Nenhum treinamento encontrado.</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Paginação --}}
        <div class="border-top px-4 pt-3 pb-1">
            {{ $treinamentos->withQueryString()->links() }}
        </div>
    </div>

    {{-- MODAL CONFIGURAR TREINAMENTO --}}
    <div class="modal fade" id="modalTreinamento" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold" id="modalTitulo"><i class="bi bi-gear-fill me-2"></i>Configurar
                        Treinamento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formTreinamento" action="{{ route('treinamentos.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="metodoForm" value="POST">
                    <input type="hidden" name="tarefas_sequencia" id="tarefas_sequencia" value="">

                    <div class="modal-body p-4">
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-uppercase">Nome do Treinamento <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nome" id="nome" class="form-control form-control-lg" required
                                    maxlength="100" placeholder="Ex: Treinamento de Desenvolvedor" />
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check form-switch mb-2 ms-3">
                                    <input type="hidden" name="status" value="0">
                                    <input class="form-check-input" type="checkbox" name="status" id="ativo" value="1"
                                        checked style="transform: scale(1.3)" />
                                    <label class="form-check-label ms-2 fw-bold" for="ativo" id="labelAtivo">Treinamento
                                        Ativo</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase">Descrição</label>
                            <textarea name="descricao" id="descricao" class="form-control" rows="2" maxlength="255"
                                placeholder="Descreva o objetivo deste treinamento..."></textarea>
                        </div>
                        <hr />

                        {{-- DUAL LIST BOX --}}
                        <div class="row mt-4">
                            <div class="col-md-6 border-end">
                                <label class="form-label fw-bold text-primary mb-2">Tarefas Disponíveis</label>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-7"><input type="text" id="filtroNomeTarefa"
                                            class="form-control form-control-sm" placeholder="Buscar tarefa..."
                                            onkeyup="atualizarListasModal()" /></div>
                                    <div class="col-md-5">
                                        <select id="filtroCatTarefa" class="form-select form-select-sm"
                                            onchange="atualizarListasModal()">
                                            <option value="">Todas Categorias</option>
                                            @foreach($categorias as $cat)
                                                <option value="{{ $cat->nome }}">{{ $cat->nome }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="dual-list-box list-group list-group-flush shadow-sm" id="listaDisponiveis">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold text-success mb-0">Sequência do treinamento <span
                                            class="text-danger">*</span></label>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-info text-dark" id="tempoTotalModal"><i
                                                class="bi bi-clock me-1"></i>0h</span>
                                        <span class="badge bg-success" id="countSelecionadas">0</span>
                                    </div>
                                </div>
                                <div class="dual-list-box list-group list-group-flush shadow-sm border-success bg-light"
                                    id="listaSelecionadas"></div>
                                <small class="text-danger mt-2 d-none" id="erroListaVazia">Selecione pelo menos uma tarefa
                                    para compor o treinamento.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light p-3">
                        <button type="button" class="btn btn-secondary px-4 fw-bold"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary px-5 fw-bold shadow"
                            onclick="submeterTreinamento()">Salvar Treinamento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL VISUALIZAR --}}
    <div class="modal fade" id="modalVisualizarTreinamento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-eye me-2"></i>Visualizar Treinamento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-8">
                            <span class="view-label">Nome do Treinamento</span>
                            <h4 class="fw-bold text-dark" id="viewNome"></h4>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="view-label">Status</span>
                            <span id="viewStatus"></span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="view-label">Descrição</span>
                        <div class="view-content" id="viewDescricao"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <span class="view-label">Total de Tarefas Ativas</span>
                            <div class="h5 fw-bold" id="viewTotalTarefas">0</div>
                        </div>
                        <div class="col-6 text-end">
                            <span class="view-label">Tempo Estimado Ativo</span>
                            <div class="h5 fw-bold text-primary" id="viewTempoTotal">0h</div>
                        </div>
                    </div>
                    <span class="view-label">Tarefas Vinculadas (Ordem de Execução)</span>
                    <div class="list-group shadow-sm" id="viewListaTarefas"></div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
        const tarefasDisponiveisDB = @json($tarefasDisponiveis);
        const treinamentosDB = @json($treinamentos->items());

        let selecionadasID = [];
        let isTreinamentoEmTrilha = false;
        let sortableInstance = null;

        const bootstrapModalConfig = new bootstrap.Modal(document.getElementById('modalTreinamento'));

        // Função utilitária para converter decimal em formato h m
        function formatarTempoVisual(horasDecimal) {
            if (!horasDecimal) return '0h';
            const h = Math.floor(horasDecimal);
            const m = Math.round((horasDecimal - h) * 60);
            if (h === 0) return `${m}m`;
            if (m === 0) return `${h}h`;
            return `${h}h ${m}m`;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const searchForm = document.getElementById('searchForm');
            let timeout = null;

            document.getElementById('searchInput').addEventListener('keyup', function () {
                clearTimeout(timeout);
                timeout = setTimeout(() => searchForm.submit(), 500);
            });

            document.getElementById('filtroStatus').addEventListener('change', function () {
                searchForm.submit();
            });

            document.getElementById('ativo').addEventListener('change', function () {
                document.getElementById('labelAtivo').innerText = this.checked ? 'Treinamento Ativo' : 'Treinamento Inativo';
                document.getElementById('labelAtivo').className = this.checked ? 'form-check-label ms-2 fw-bold text-success' : 'form-check-label ms-2 fw-bold text-secondary';
            });

            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    const form = this.closest('form');
                    const titulo = this.closest('.card').querySelector('h5').innerText;

                    Swal.fire({
                        title: 'Excluir Treinamento?',
                        html: `Deseja realmente remover <strong>"${titulo}"</strong>?<br><small class="text-danger mt-2 d-block">Atenção: Você não poderá excluir se ele estiver vinculado a um plano de estudos ou treinamento.</small>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#1e2d40',
                        confirmButtonText: 'Sim, excluir!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });

            @if($errors->any())
                prepararNovoTreinamento();
                bootstrapModalConfig.show();
            @endif
                        });

        function prepararNovoTreinamento() {
            document.getElementById("formTreinamento").reset();
            document.getElementById("formTreinamento").action = "{{ route('treinamentos.store') }}";
            document.getElementById("metodoForm").value = "POST";
            document.getElementById("modalTitulo").innerHTML = '<i class="bi bi-plus-circle me-2"></i>Novo Treinamento';
            document.getElementById("ativo").checked = true;
            document.getElementById("labelAtivo").innerText = "Treinamento Ativo";
            document.getElementById("labelAtivo").className = "form-check-label ms-2 fw-bold text-success";

            selecionadasID = [];
            isTreinamentoEmTrilha = false;
            document.getElementById("erroListaVazia").classList.add('d-none');
            atualizarListasModal();
        }

        function editarTreinamento(id, nome, descricao, status, tarefasIdsArray, emTrilha) {
            document.getElementById("formTreinamento").action = `/admin/treinamentos/${id}`;
            document.getElementById("metodoForm").value = "PUT";
            document.getElementById("modalTitulo").innerHTML = '<i class="bi bi-pencil-square me-2"></i>Editar Treinamento';

            document.getElementById("nome").value = nome;
            document.getElementById("descricao").value = descricao;

            const checkAtivo = document.getElementById("ativo");
            checkAtivo.checked = status === 1;
            document.getElementById("labelAtivo").innerText = status === 1 ? "Treinamento Ativo" : "Treinamento Inativo";
            document.getElementById("labelAtivo").className = status === 1 ? "form-check-label ms-2 fw-bold text-success" : "form-check-label ms-2 fw-bold text-secondary";

            // Garante que a lista de edição só receba IDs válidos
            selecionadasID = Array.isArray(tarefasIdsArray) ? tarefasIdsArray : JSON.parse(tarefasIdsArray);
            isTreinamentoEmTrilha = emTrilha;

            atualizarListasModal();
            bootstrapModalConfig.show();
        }

        function calcularTempoTotal(ids) {
            return ids.reduce((acc, id) => {
                const t = tarefasDisponiveisDB.find((x) => x.id == id);
                return acc + (t ? parseFloat(t.tempo_estimado) : 0);
            }, 0);
        }

        function atualizarListasModal() {
            const buscaNome = document.getElementById("filtroNomeTarefa").value.toLowerCase();
            const buscaCat = document.getElementById("filtroCatTarefa").value;
            const listaDisp = document.getElementById("listaDisponiveis");
            const listaSel = document.getElementById("listaSelecionadas");

            // COLUNA DA ESQUERDA
            const disponiveis = tarefasDisponiveisDB.filter(t =>
                !selecionadasID.includes(t.id) &&
                t.titulo.toLowerCase().includes(buscaNome) &&
                (buscaCat === "" || (t.categoria && t.categoria.nome === buscaCat))
            );

            listaDisp.innerHTML = disponiveis.map(t => {
                const catBadgeCor = t.categoria ? t.categoria.cor_hex : '#6c757d';
                const catNome = t.categoria ? t.categoria.nome : 'Geral';
                return `
                                                <button type="button" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center" onclick="adicionar(${t.id})">
                                                    <div class="d-flex align-items-center text-start overflow-hidden pe-2">
                                                        <i class="bi bi-plus-circle-fill text-primary me-2"></i>
                                                        <strong class="me-2 text-truncate" title="${t.titulo}">${t.titulo}</strong>
                                                        <span class="badge flex-shrink-0" style="background-color: ${catBadgeCor}; font-size: 0.65rem;">${catNome}</span>
                                                    </div>
                                                    <span class="badge bg-light text-muted border flex-shrink-0">${formatarTempoVisual(t.tempo_estimado)}</span>
                                                </button>`;
            }).join("") || '<div class="text-center p-4 text-muted small">Nenhuma tarefa encontrada com os filtros atuais.</div>';

            // COLUNA DA DIREITA (CORREÇÃO DE CONTAGEM AQUI)
            let validCount = 0;
            let htmlSelecionadas = '';

            selecionadasID.forEach((id) => {
                const t = tarefasDisponiveisDB.find((x) => x.id == id);
                if (t) {
                    validCount++; // Só incrementa se a tarefa existir e estiver ativa (pois tarefasDisponiveisDB só tem ativas)

                    const catBadgeCor = t.categoria ? t.categoria.cor_hex : '#6c757d';
                    const catNome = t.categoria ? t.categoria.nome : 'Geral';

                    const btnRemover = isTreinamentoEmTrilha
                        ? `<button type="button" class="btn btn-sm btn-link text-muted text-decoration-none p-0 flex-shrink-0" onclick="bloquearRemocao()"><i class="bi bi-lock-fill fs-5"></i></button>`
                        : `<button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0 flex-shrink-0" onclick="remover(${t.id})"><i class="bi bi-x-lg fw-bold fs-5"></i></button>`;

                    const dragIcon = isTreinamentoEmTrilha ? '' : '<i class="bi bi-grip-vertical text-muted me-1 drag-handle cursor-grab fs-5 flex-shrink-0"></i>';

                    htmlSelecionadas += `
                                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-2 border-0 border-bottom bg-transparent" data-id="${t.id}">
                                                        <div class="d-flex align-items-center overflow-hidden pe-2 w-100">
                                                            ${dragIcon}
                                                            <span class="step-number">${validCount}</span>
                                                            <strong class="me-2 text-truncate" title="${t.titulo}">${t.titulo}</strong> 
                                                            <span class="badge me-2 flex-shrink-0" style="background-color: ${catBadgeCor}; font-size: 0.65rem;">${catNome}</span>
                                                            <small class="text-muted text-nowrap">(${formatarTempoVisual(t.tempo_estimado)})</small>
                                                        </div>
                                                        ${btnRemover}
                                                    </div>`;
                }
            });

            listaSel.innerHTML = htmlSelecionadas || '<div class="text-center mt-5 text-muted small"><i class="bi bi-arrow-left-circle mb-2 d-block fs-3"></i>Clique nas tarefas ao lado para montar a jornada.</div>';

            document.getElementById("tempoTotalModal").innerHTML = `<i class="bi bi-clock me-1"></i> ${formatarTempoVisual(calcularTempoTotal(selecionadasID))}`;
            document.getElementById("countSelecionadas").innerText = validCount;

            inicializarDragAndDrop();
        }

        function inicializarDragAndDrop() {
            const el = document.getElementById('listaSelecionadas');
            if (sortableInstance) sortableInstance.destroy();

            if (!isTreinamentoEmTrilha) {
                sortableInstance = Sortable.create(el, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: function () {
                        const items = document.querySelectorAll('#listaSelecionadas .list-group-item');
                        selecionadasID = Array.from(items).map(item => parseInt(item.getAttribute('data-id')));
                        atualizarListasModal(); // Força a atualização da "bolinha de número" para a nova ordem
                    }
                });
            }
        }

        function adicionar(id) {
            selecionadasID.push(id);
            document.getElementById("erroListaVazia").classList.add('d-none');
            atualizarListasModal();
        }

        function remover(id) {
            selecionadasID = selecionadasID.filter((i) => i != id);
            atualizarListasModal();
        }

        function bloquearRemocao() {
            Swal.fire({
                icon: 'error',
                title: 'Ação Bloqueada',
                text: 'Este treinamento já está vinculado a uma Trilha de Aprendizagem. Não é possível alterar a grade (remover ou reordenar) para manter o histórico dos alunos.',
                confirmButtonColor: '#e85d2f'
            });
        }

        function submeterTreinamento() {
            const form = document.getElementById("formTreinamento");

            // Validação nativa de HTML (required, maxlength, etc.)
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }

            // Remove do array IDs que possam estar sujos (inativos/apagados) antes de enviar para o banco
            const selecionadasValidas = selecionadasID.filter(id => tarefasDisponiveisDB.some(t => t.id == id));

            if (selecionadasValidas.length === 0) {
                document.getElementById("erroListaVazia").classList.remove('d-none');
                return false;
            }

            document.getElementById("tarefas_sequencia").value = JSON.stringify(selecionadasValidas);
            form.submit();
        }

        // === FUNÇÃO DE VISUALIZAÇÃO ===
        function visualizarTreinamento(id) {
            const t = treinamentosDB.find(x => x.id == id);
            if (!t) return;

            // FILTRA AS TAREFAS ATIVAS AQUI PARA A MODAL DE VISÃO
            const tarefasAtivas = t.tarefas.filter(tarefa => tarefa.status == 1);

            document.getElementById("viewNome").innerText = t.nome;
            document.getElementById("viewDescricao").innerText = t.descricao || "Nenhuma descrição informada.";
            document.getElementById("viewStatus").innerHTML = t.status
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">Ativo</span>'
                : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">Inativo</span>';

            document.getElementById("viewTotalTarefas").innerText = tarefasAtivas.length;

            const tempoTotalObj = tarefasAtivas.reduce((acc, t_relacionamento) => acc + parseFloat(t_relacionamento.tempo_estimado), 0);
            document.getElementById("viewTempoTotal").innerText = formatarTempoVisual(tempoTotalObj);

            const listaView = document.getElementById("viewListaTarefas");
            listaView.innerHTML = tarefasAtivas.map((tarefaRel, idx) => {
                const catNome = tarefaRel.categoria ? tarefaRel.categoria.nome : 'Sem Categoria';
                const catCor = tarefaRel.categoria ? tarefaRel.categoria.cor_hex : '#6c757d';

                return `
                                                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                                  <div class="d-flex align-items-center overflow-hidden pe-3">
                                                    <span class="step-number">${idx + 1}</span>
                                                    <strong class="me-2 text-truncate" title="${tarefaRel.titulo}">${tarefaRel.titulo}</strong>
                                                    <span class="badge flex-shrink-0" style="background-color: ${catCor}; font-size: 0.65rem;">${catNome}</span>
                                                  </div>
                                                  <span class="text-muted fw-bold text-nowrap"><i class="bi bi-clock me-1"></i>${formatarTempoVisual(tarefaRel.tempo_estimado)}</span>
                                                </div>`;
            }).join("") || '<div class="text-center p-3 text-muted">Nenhuma tarefa ativa associada a este treinamento.</div>';

            new bootstrap.Modal(document.getElementById('modalVisualizarTreinamento')).show();
        }

    </script>
@endpush