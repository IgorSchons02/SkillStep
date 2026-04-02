@extends('layout.app')

@section('titulo', 'Gestão de Trilhas')

@section('content')
    <div class="container-fluid">
        {{-- Cabeçalho --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark"><i class="bi bi-signpost-split-fill text-primary me-2"></i>Trilhas</h2>
                <p class="text-muted mb-0">Agrupe treinamentos em jornadas sequenciais de formação para os colaboradores.
                </p>
            </div>
            <button type="button" class="btn btn-primary px-4 shadow-sm fw-bold" data-bs-toggle="modal"
                data-bs-target="#modalTrilha" onclick="prepararNovaTrilha()">
                <i class="bi bi-plus-lg me-2"></i>Nova Trilha
            </button>
        </div>

        {{-- Filtros (Real-time) --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form action="{{ route('trilhas.index') }}" method="GET" id="searchForm" class="row g-2">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" id="searchInput" class="form-control bg-light border-0"
                                placeholder="Pesquisar trilhas..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <select name="status" id="filtroStatus" class="form-select bg-light border-0">
                            <option value="">Todos os Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Ativas</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inativas</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        {{-- Grid de Trilhas --}}
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
            @forelse($trilhas as $trilha)
                @php
                    // Filtramos os treinamentos ativos para a contagem no card
                    $treinamentosAtivos = $trilha->treinamentos->where('status', 1)->values();

                    // A carga horária da trilha é a soma do tempo estimado de todas as tarefas de todos os treinamentos ativos nela
                    $cargaHoraria = 0;
                    foreach ($treinamentosAtivos as $treinamento) {
                        $cargaHoraria += $treinamento->tarefas->where('status', 1)->sum('tempo_estimado');
                    }
                @endphp
                <div class="col">
                    <div class="card h-100 shadow-sm border card-trilha">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <div class="d-flex gap-1">
                                    <span class="badge bg-primary">{{ $treinamentosAtivos->count() }} Treinamentos</span>
                                    <span class="badge bg-info text-dark"><i class="bi bi-clock"></i>
                                        {{ number_format($cargaHoraria, 1, ',', '') }}h</span>
                                </div>
                                <span
                                    class="badge {{ $trilha->status ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} border">
                                    {{ $trilha->status ? 'Ativa' : 'Inativa' }}
                                </span>
                            </div>
                            <h5 class="fw-bold mb-2">{{ $trilha->nome }}</h5>
                            <p class="text-muted small mb-0 text-truncate" style="max-height: 40px;">
                                {{ $trilha->descricao ?? 'Sem descrição informada.' }}
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3 d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-sm btn-light border px-3 text-primary"
                                onclick="visualizarTrilha({{ $trilha->id }})" title="Visualizar a formação">
                                <i class="bi bi-eye"></i> Visualizar
                            </button>

                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary me-2"
                                    onclick="editarTrilha({{ $trilha->id }}, '{{ $trilha->nome }}', '{{ $trilha->descricao }}', {{ $trilha->status }}, {{ json_encode($treinamentosAtivos->pluck('id')) }}, {{ isset($trilha->tem_alunos) && $trilha->tem_alunos ? 'true' : 'false' }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('trilhas.destroy', $trilha->id) }}" method="POST"
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
                        <i class="bi bi-signpost-split text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3 text-muted">Nenhuma trilha encontrada.</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Paginação --}}
        <div class="border-top px-4 pt-3 pb-1">
            {{ $trilhas->withQueryString()->links() }}
        </div>
    </div>

    {{-- MODAL CONFIGURAR TRILHA --}}
    <div class="modal fade" id="modalTrilha" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold" id="modalTituloTrilha"><i class="bi bi-gear-fill me-2"></i>Configurar
                        Trilha</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formTrilha" action="{{ route('trilhas.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="metodoForm" value="POST">
                    <input type="hidden" name="treinamentos_sequencia" id="treinamentos_sequencia" value="">

                    <div class="modal-body p-4">
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-uppercase">Nome da Trilha <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nome" id="nomeTrilha" class="form-control form-control-lg" required
                                    maxlength="100" placeholder="Ex: Formação desenvolvedor" />
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check form-switch mb-2 ms-3">
                                    <input type="hidden" name="status" value="0">
                                    <input class="form-check-input" type="checkbox" name="status" id="statusTrilha"
                                        value="1" checked style="transform: scale(1.3)" />
                                    <label class="form-check-label ms-2 fw-bold" for="statusTrilha" id="labelAtivo">Trilha
                                        Ativa</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase">Descrição</label>
                            <textarea name="descricao" id="descricaoTrilha" class="form-control" rows="2" maxlength="255"
                                placeholder="Descreva o objetivo desta formação..."></textarea>
                        </div>
                        <hr />

                        {{-- DUAL LIST BOX --}}
                        <div class="row mt-4">
                            <div class="col-md-6 border-end">
                                <label class="form-label fw-bold text-primary mb-2">Treinamentos Disponíveis</label>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                                    <input type="text" id="filtroTreinamento" class="form-control bg-light border-0"
                                        placeholder="Buscar treinamento..." onkeyup="atualizarListasModal()" />
                                </div>
                                <div class="dual-list-box list-group list-group-flush shadow-sm" id="listaDisponiveis">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold text-success mb-0">Sequência da trilha <span
                                            class="text-danger">*</span></label>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-info text-dark" id="tempoTotalModal"><i
                                                class="bi bi-clock me-1"></i>0h</span>
                                        <span class="badge bg-success" id="countSelecionadas">0</span>
                                    </div>
                                </div>
                                <div class="dual-list-box list-group list-group-flush shadow-sm border-success bg-light"
                                    id="listaSelecionadas"></div>
                                <small class="text-danger mt-2 d-none" id="erroListaVazia">Selecione pelo menos um
                                    treinamento para compor a trilha.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light p-3">
                        <button type="button" class="btn btn-secondary px-4 fw-bold"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary px-5 fw-bold shadow" onclick="submeterTrilha()">Salvar
                            Trilha</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL VISUALIZAR --}}
    <div class="modal fade" id="modalVisualizarTrilha" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-eye me-2"></i>Visualizar Trilha</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-8">
                            <span class="view-label">Nome da Trilha</span>
                            <h4 class="fw-bold text-dark" id="viewNomeTrilha"></h4>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="view-label">Status</span>
                            <span id="viewStatusTrilha"></span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="view-label">Descrição</span>
                        <div class="view-content" id="viewDescricaoTrilha"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <span class="view-label">Total de Treinamentos Ativos</span>
                            <div class="h5 fw-bold" id="viewTotalTreinamentos">0</div>
                        </div>
                        <div class="col-6 text-end">
                            <span class="view-label">Carga Horária Estimada</span>
                            <div class="h5 fw-bold text-primary" id="viewTempoTotalTrilha">0h</div>
                        </div>
                    </div>
                    <span class="view-label">Treinamentos Vinculados (Ordem de Execução)</span>
                    <div class="list-group shadow-sm" id="viewListaTreinamentos"></div>
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
        // === DADOS RECEBIDOS DO BACKEND ===
        // O backend já deve enviar os treinamentos com uma propriedade extra calculada: carga_horaria (soma das tarefas ativas)
        const treinamentosDisponiveisDB = @json($treinamentosDisponiveis);
        const trilhasDB = @json($trilhas->items());

        let selecionadasID = [];
        let isTrilhaEmUso = false; // Flag para travar exclusão se já houver alunos atrelados
        let sortableInstance = null;

        const bootstrapModalConfig = new bootstrap.Modal(document.getElementById('modalTrilha'));

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

            document.getElementById('statusTrilha').addEventListener('change', function () {
                document.getElementById('labelAtivo').innerText = this.checked ? 'Trilha Ativa' : 'Trilha Inativa';
                document.getElementById('labelAtivo').className = this.checked ? 'form-check-label ms-2 fw-bold text-success' : 'form-check-label ms-2 fw-bold text-secondary';
            });

            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    const form = this.closest('form');
                    const titulo = this.closest('.card').querySelector('h5').innerText;

                    Swal.fire({
                        title: 'Excluir Trilha?',
                        html: `Deseja realmente remover <strong>"${titulo}"</strong>?<br><small class="text-danger mt-2 d-block">Atenção: Você não poderá excluir se houver planos de estudos vinculados a esta trilha.</small>`,
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
                prepararNovaTrilha();
                bootstrapModalConfig.show();
            @endif
                                });

        function prepararNovaTrilha() {
            document.getElementById("formTrilha").reset();
            document.getElementById("formTrilha").action = "{{ route('trilhas.store') }}";
            document.getElementById("metodoForm").value = "POST";
            document.getElementById("modalTituloTrilha").innerHTML = '<i class="bi bi-plus-circle me-2"></i>Nova Trilha';
            document.getElementById("statusTrilha").checked = true;
            document.getElementById("labelAtivo").innerText = "Trilha Ativa";
            document.getElementById("labelAtivo").className = "form-check-label ms-2 fw-bold text-success";

            selecionadasID = [];
            isTrilhaEmUso = false;
            document.getElementById("erroListaVazia").classList.add('d-none');
            atualizarListasModal();
        }

        function editarTrilha(id, nome, descricao, status, treinamentosIdsArray, emUso) {
            document.getElementById("formTrilha").action = `/admin/trilhas/${id}`;
            document.getElementById("metodoForm").value = "PUT";
            document.getElementById("modalTituloTrilha").innerHTML = '<i class="bi bi-pencil-square me-2"></i>Editar Trilha';

            document.getElementById("nomeTrilha").value = nome;
            document.getElementById("descricaoTrilha").value = descricao;

            const checkAtivo = document.getElementById("statusTrilha");
            checkAtivo.checked = status === 1;
            document.getElementById("labelAtivo").innerText = status === 1 ? "Trilha Ativa" : "Trilha Inativa";
            document.getElementById("labelAtivo").className = status === 1 ? "form-check-label ms-2 fw-bold text-success" : "form-check-label ms-2 fw-bold text-secondary";

            selecionadasID = Array.isArray(treinamentosIdsArray) ? treinamentosIdsArray : JSON.parse(treinamentosIdsArray);
            isTrilhaEmUso = emUso;

            atualizarListasModal();
            bootstrapModalConfig.show();
        }

        function calcularCargaHorariaTotal(ids) {
            return ids.reduce((acc, id) => {
                const t = treinamentosDisponiveisDB.find((x) => x.id == id);
                return acc + (t ? parseFloat(t.carga_horaria) : 0);
            }, 0);
        }

        function atualizarListasModal() {
            const buscaNome = document.getElementById("filtroTreinamento").value.toLowerCase();
            const listaDisp = document.getElementById("listaDisponiveis");
            const listaSel = document.getElementById("listaSelecionadas");

            // COLUNA DA ESQUERDA
            const disponiveis = treinamentosDisponiveisDB.filter(t =>
                !selecionadasID.includes(t.id) &&
                t.nome.toLowerCase().includes(buscaNome)
            );

            listaDisp.innerHTML = disponiveis.map(t => {
                return `
                                        <button type="button" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center" onclick="adicionar(${t.id})">
                                            <div class="d-flex align-items-center text-start overflow-hidden pe-2">
                                                <i class="bi bi-plus-circle-fill text-primary me-2"></i>
                                                <strong class="me-2 text-truncate" title="${t.nome}">${t.nome}</strong>
                                            </div>
                                            <span class="badge bg-light text-muted border flex-shrink-0">${t.carga_horaria}h</span>
                                        </button>`;
            }).join("") || '<div class="text-center p-4 text-muted small">Nenhum treinamento encontrado.</div>';

            // COLUNA DA DIREITA
            let validCount = 0;
            let htmlSelecionadas = '';

            selecionadasID.forEach((id) => {
                const t = treinamentosDisponiveisDB.find((x) => x.id == id);
                if (t) {
                    validCount++;

                    const btnRemover = isTrilhaEmUso
                        ? `<button type="button" class="btn btn-sm btn-link text-muted text-decoration-none p-0 flex-shrink-0" onclick="bloquearRemocao()"><i class="bi bi-lock-fill fs-5"></i></button>`
                        : `<button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0 flex-shrink-0" onclick="remover(${t.id})"><i class="bi bi-x-lg fw-bold fs-5"></i></button>`;

                    const dragIcon = isTrilhaEmUso ? '' : '<i class="bi bi-grip-vertical text-muted me-1 drag-handle cursor-grab fs-5 flex-shrink-0"></i>';

                    htmlSelecionadas += `
                                            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-2 border-0 border-bottom bg-transparent" data-id="${t.id}">
                                                <div class="d-flex align-items-center overflow-hidden pe-2 w-100">
                                                    ${dragIcon}
                                                    <span class="step-number">${validCount}</span>
                                                    <strong class="me-2 text-truncate" title="${t.nome}">${t.nome}</strong> 
                                                    <small class="text-muted text-nowrap">(${t.carga_horaria}h)</small>
                                                </div>
                                                ${btnRemover}
                                            </div>`;
                }
            });

            listaSel.innerHTML = htmlSelecionadas || '<div class="text-center mt-5 text-muted small"><i class="bi bi-arrow-left-circle mb-2 d-block fs-3"></i>Clique nos treinamentos ao lado para montar a trilha.</div>';

            document.getElementById("tempoTotalModal").innerHTML = `<i class="bi bi-clock me-1"></i> ${calcularCargaHorariaTotal(selecionadasID).toFixed(1)}h`;
            document.getElementById("countSelecionadas").innerText = validCount;

            inicializarDragAndDrop();
        }

        function inicializarDragAndDrop() {
            const el = document.getElementById('listaSelecionadas');
            if (sortableInstance) sortableInstance.destroy();

            if (!isTrilhaEmUso) {
                sortableInstance = Sortable.create(el, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: function () {
                        const items = document.querySelectorAll('#listaSelecionadas .list-group-item');
                        selecionadasID = Array.from(items).map(item => parseInt(item.getAttribute('data-id')));
                        atualizarListasModal();
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
                text: 'Esta trilha já possui alunos vinculados. Não é possível alterar a grade (remover ou reordenar treinamentos) para não comprometer o histórico de aprendizagem.',
                confirmButtonColor: '#e85d2f'
            });
        }

        function submeterTrilha() {
            const form = document.getElementById("formTrilha");
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }

            const selecionadasValidas = selecionadasID.filter(id => treinamentosDisponiveisDB.some(t => t.id == id));

            if (selecionadasValidas.length === 0) {
                document.getElementById("erroListaVazia").classList.remove('d-none');
                return false;
            }
            document.getElementById("treinamentos_sequencia").value = JSON.stringify(selecionadasValidas);
            form.submit();
        }

        // === FUNÇÃO DE VISUALIZAÇÃO ===
        function visualizarTrilha(id) {
            const t = trilhasDB.find(x => x.id == id);
            if (!t) return;

            // FILTRA OS TREINAMENTOS ATIVOS AQUI PARA A MODAL DE VISÃO
            const treinamentosAtivos = t.treinamentos.filter(treino => treino.status == 1);

            document.getElementById("viewNomeTrilha").innerText = t.nome;
            document.getElementById("viewDescricaoTrilha").innerText = t.descricao || "Nenhuma descrição informada.";
            document.getElementById("viewStatusTrilha").innerHTML = t.status
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">Ativa</span>'
                : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">Inativa</span>';

            document.getElementById("viewTotalTreinamentos").innerText = treinamentosAtivos.length;

            // Calcula a carga horária somando apenas as tarefas ativas dos treinamentos ativos
            let cargaHorariaObj = 0;
            treinamentosAtivos.forEach(treinamento => {
                const tarefasAtivas = treinamento.tarefas.filter(tarefa => tarefa.status == 1);
                cargaHorariaObj += tarefasAtivas.reduce((acc, tf) => acc + parseFloat(tf.tempo_estimado), 0);
            });

            document.getElementById("viewTempoTotalTrilha").innerText = cargaHorariaObj.toFixed(1) + 'h';

            const listaView = document.getElementById("viewListaTreinamentos");
            listaView.innerHTML = treinamentosAtivos.map((treinoRel, idx) => {

                // Calcula o tempo deste treinamento específico para mostrar na listagem da visualização
                const tarefasDesseTreino = treinoRel.tarefas.filter(tarefa => tarefa.status == 1);
                const tempoDesseTreino = tarefasDesseTreino.reduce((acc, tf) => acc + parseFloat(tf.tempo_estimado), 0);

                return `
                                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                          <div class="d-flex align-items-center overflow-hidden pe-3">
                                            <span class="step-number">${idx + 1}</span>
                                            <strong class="me-2 text-truncate" title="${treinoRel.nome}">${treinoRel.nome}</strong>
                                          </div>
                                          <span class="badge bg-light text-muted border flex-shrink-0"><i class="bi bi-clock me-1"></i>${tempoDesseTreino.toFixed(1)}h</span>
                                        </div>`;
            }).join("") || '<div class="text-center p-3 text-muted">Nenhum treinamento ativo associado a esta trilha.</div>';

            new bootstrap.Modal(document.getElementById('modalVisualizarTrilha')).show();
        }

    </script>
@endpush