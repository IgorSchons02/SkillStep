@extends('layout.app')

@section('titulo', 'Planos de Estudo')

@push('css')
    {{-- Select2 para busca inteligente com suporte a Múltipla Seleção --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
@endpush

@section('content')
    <div class="container-fluid">
        {{-- Cabeçalho --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark"><i class="bi bi-person-workspace text-primary me-2"></i>Planos de Estudo</h2>
                <p class="text-muted mb-0">Personalize jornadas de aprendizado. Itens concluídos ficam bloqueados para
                    remoção.</p>
            </div>
            <button type="button" class="btn btn-primary px-4 shadow-sm fw-bold" data-bs-toggle="modal"
                data-bs-target="#modalPlano" onclick="prepararNovoPlano()">
                <i class="bi bi-plus-lg me-2"></i>Novo Plano
            </button>
        </div>

        {{-- Filtros --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form action="{{ route('planos.index') }}" method="GET" id="searchForm" class="row g-2">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" id="pesquisaPlanoGrid" class="form-control bg-light border-0"
                                placeholder="Pesquisar por aluno, cpf ou título do plano..."
                                value="{{ request('search') }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="status" id="filtroStatus" class="form-select bg-light border-0">
                            <option value="">Todos os Status</option>
                            <option value="andamento" {{ request('status') === 'andamento' ? 'selected' : '' }}>Em Andamento
                            </option>
                            <option value="concluido" {{ request('status') === 'concluido' ? 'selected' : '' }}>Concluídos
                            </option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        {{-- Grid de Planos gerada pelo JavaScript --}}
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4" id="gridPlanos"></div>

        <div class="border-top px-4 pt-3 pb-1">
            {{ $planos->withQueryString()->links() }}
        </div>
    </div>

    {{-- MODAL CONFIGURAR PLANO --}}
    <div class="modal fade" id="modalPlano" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold" id="modalTitulo"><i class="bi bi-diagram-3-fill me-2"></i>Configurar
                        Plano de Estudos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form id="formPlano" action="{{ route('planos.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="metodoForm" value="POST">
                    <input type="hidden" name="estrutura" id="estrutura_json" value="">

                    <div class="modal-body p-4">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Título do Plano <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="titulo" id="tituloPlano" class="form-control" required
                                    placeholder="Ex: Trilha de Nivelamento Backend" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Aluno <span
                                        class="text-danger">*</span></label>
                                <select name="usuario_id" id="alunoPlano" class="form-select select2-busca" required>
                                    <option value="">Pesquise o aluno...</option>
                                    @foreach($alunos as $al)
                                        <option value="{{ $al->id }}">{{ $al->nome }} ({{ $al->cpf }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Supervisores</label>
                                <select name="supervisores[]" id="supervisoresPlano" class="form-select select2-multipla"
                                    multiple data-placeholder="Escolha um ou mais...">
                                    @foreach($supervisores as $sup)
                                        <option value="{{ $sup->id }}">{{ $sup->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="totalizador-geral d-flex justify-content-between align-items-center shadow-sm">
                            <div>
                                <span class="fw-bold text-uppercase small text-muted d-block">Carga Horária Total:</span>
                                <span class="h4 mb-0 fw-bold text-primary" id="tempoTotalGeral">0h</span>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-uppercase small text-muted d-block">Progresso Estimado:</span>
                                <span class="h5 mb-0 fw-bold text-success" id="progressoGeralModal">0%</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label fw-bold text-uppercase text-muted mb-0">Estrutura de
                                Aprendizado</label>
                            <button type="button" class="btn btn-sm btn-outline-primary fw-bold shadow-sm"
                                onclick="abrirBusca('trilha')">
                                <i class="bi bi-plus-circle me-1"></i> Adicionar Trilha
                            </button>
                        </div>

                        <div class="tree-container bg-light" id="treeViewContainer"></div>
                    </div>
                    <div class="modal-footer border-0 bg-white p-3">
                        <button type="button" class="btn btn-secondary px-4 fw-bold"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-5 fw-bold shadow">Salvar Plano</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL BUSCA DE ITENS --}}
    <div class="modal fade" id="modalBusca" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white"><i class="bi bi-plus-circle me-2"></i>
                    <h5 class="modal-title fw-bold" id="tituloBusca">Adicionar Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3 d-none" id="containerDataSugerida">
                        <label class="form-label fw-bold small text-uppercase text-muted">Data Sugerida para Conclusão <span
                                class="text-danger">*</span></label>
                        <input type="date" id="dataSugeridaTrilha" class="form-control border-primary" />
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="filtroItemBusca" class="form-control bg-light border-0"
                            placeholder="Pesquisar itens ativos..." onkeyup="filtrarListaBusca()">
                    </div>
                    <div class="list-group shadow-sm" id="listaBuscaItens" style="max-height: 300px; overflow-y: auto;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL VISUALIZAR (Para o Grid) --}}
    <div class="modal fade" id="modalVisualizar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-eye me-2"></i>Visualizar Andamento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="infoPlanoVis" class="mb-4">
                        <h4 class="fw-bold mb-1" id="visTitulo"></h4>
                        <p class="text-muted mb-0"><i class="bi bi-person me-1"></i> Aluno: <span id="visAluno"
                                class="fw-bold text-dark"></span></p>

                        <div class="mt-4 p-3 bg-light rounded border">
                            <div class="row align-items-center mb-2">
                                <div class="col-6">
                                    <span class="small text-muted fw-bold text-uppercase">Carga Horária Estimada</span><br>
                                    <span class="fw-bold fs-5 text-primary" id="visCargaHoraria"><i
                                            class="bi bi-clock me-1"></i>0h</span>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="small text-muted fw-bold text-uppercase">Progresso Geral</span><br>
                                    <span class="fw-bold fs-5" id="visPercent">0%</span>
                                </div>
                            </div>
                            <div class="progress" style="height: 10px;" id="visProgressContainer"></div>
                        </div>
                    </div>

                    <label class="form-label fw-bold small text-uppercase text-muted">Estrutura de Aprendizado</label>
                    <div class="tree-container" id="treeViewContainerVis"></div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
        // Conexão direta com os dados do Laravel
        const db = @json($db);
        let listaPlanos = @json($planos->items());
        const listaSupervisores = @json($supervisores);

        let planoEdicao = { trilhas: [] };
        let contextoAdicao = {};
        let itensExibidosBusca = [];

        const modalBusca = new bootstrap.Modal(document.getElementById('modalBusca'));
        const bootstrapModalPlano = new bootstrap.Modal(document.getElementById('modalPlano'));
        const bootstrapModalVis = new bootstrap.Modal(document.getElementById('modalVisualizar'));

        document.addEventListener('DOMContentLoaded', function () {
            // Inicializa Select2 para busca única (Aluno)
            $('.select2-busca').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalPlano')
            });

            // Inicializa Select2 para seleção múltipla (Supervisores)
            $('.select2-multipla').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalPlano'),
                placeholder: $(this).data('placeholder'),
                closeOnSelect: false
            });

            const searchForm = document.getElementById('searchForm');
            let timeout = null;

            document.getElementById('pesquisaPlanoGrid').addEventListener('keyup', function () {
                clearTimeout(timeout);
                timeout = setTimeout(() => searchForm.submit(), 500);
            });

            document.getElementById('filtroStatus').addEventListener('change', function () {
                searchForm.submit();
            });

            renderGrid();

            // Se der erro de validação no Laravel, reabre a modal
            @if($errors->any())
                prepararNovoPlano();
                bootstrapModalPlano.show();

                Swal.fire({
                    title: 'Atenção!',
                    text: '{{ $errors->first() }}',
                    icon: 'warning',
                    confirmButtonColor: '#e85d2f'
                });
            @endif
                });

        function formatarTempoVisual(horasDecimal) {
            if (!horasDecimal) return '0h';
            const h = Math.floor(horasDecimal);
            const m = Math.round((horasDecimal - h) * 60);
            if (h === 0) return `${m}m`;
            if (m === 0) return `${h}h`;
            return `${h}h ${m}m`;
        }

        function calcularProgressoTempo(estrutura) {
            let total = 0; let concluido = 0;
            if (estrutura.trilhas) {
                estrutura.trilhas.forEach(trilha => {
                    if (trilha.treinamentos) {
                        trilha.treinamentos.forEach(treino => {
                            if (treino.tarefas) {
                                treino.tarefas.forEach(tarefa => {
                                    const tempo = parseFloat(tarefa.tempo_estimado) || 0;
                                    total += tempo;
                                    if (tarefa.concluido) concluido += tempo;
                                });
                            }
                        });
                    }
                });
            }
            return { total, concluido, percentual: total === 0 ? 0 : Math.round((concluido / total) * 100) };
        }

        // === GESTÃO DO PLANO ===
        function prepararNovoPlano() {
            document.getElementById("formPlano").reset();
            document.getElementById("formPlano").action = "{{ route('planos.store') }}";
            document.getElementById("metodoForm").value = "POST";

            $('#alunoPlano').val(null).trigger('change');
            $('#supervisoresPlano').val(null).trigger('change');

            planoEdicao = { trilhas: [] };
            renderTreeView();
        }

        function editarPlano(id) {
            const p = listaPlanos.find(x => x.id === id);
            if (!p) return;

            document.getElementById("formPlano").action = `/admin/planos/${id}`;
            document.getElementById("metodoForm").value = "PUT";
            document.getElementById("tituloPlano").value = p.titulo;

            $('#alunoPlano').val(p.usuario_id).trigger('change');

            let supervisoresIds = [];
            if (p.supervisores) {
                supervisoresIds = p.supervisores.map(sup => sup.id);
            } else if (p.supervisores_ids) {
                supervisoresIds = p.supervisores_ids;
            }
            $('#supervisoresPlano').val(supervisoresIds).trigger('change');

            planoEdicao = p.estrutura && p.estrutura.trilhas ? JSON.parse(JSON.stringify(p.estrutura)) : { trilhas: [] };

            renderTreeView();
            bootstrapModalPlano.show();
        }

        // === SUBMETER FORMULÁRIO ===
        document.getElementById("formPlano").addEventListener("submit", function (e) {
            e.preventDefault();

            if (planoEdicao.trilhas.length === 0) {
                Swal.fire({ icon: 'error', title: 'Plano Vazio', text: 'Você precisa adicionar pelo menos uma Trilha ao plano de estudos.' });
                return false;
            }

            document.getElementById("estrutura_json").value = JSON.stringify(planoEdicao);
            this.submit();
        });

        // === TREE VIEW E DRAG AND DROP ===
        function renderTreeView() {
            const container = document.getElementById("treeViewContainer");
            const progresso = calcularProgressoTempo(planoEdicao);

            if (planoEdicao.trilhas.length === 0) {
                container.innerHTML = `<div class="text-center py-5 text-muted"><i class="bi bi-tree fs-1 d-block mb-3"></i>A estrutura do plano está vazia. Adicione uma trilha para começar.</div>`;
            } else {
                container.innerHTML = planoEdicao.trilhas.map((trilha, idxT) => {
                    const tTri = trilha.treinamentos ? trilha.treinamentos.reduce((s, tr) => s + (tr.tarefas ? tr.tarefas.reduce((st, ta) => st + parseFloat(ta.tempo_estimado || 0), 0) : 0), 0) : 0;

                    const btnExcluirTrilha = !trilha.concluido ? `<button type="button" class="btn btn-sm text-danger ms-2 p-1" onclick="removerItem('trilha', ${idxT}, event)"><i class="bi bi-trash fs-5"></i></button>` : '<i class="bi bi-lock-fill text-muted ms-3"></i>';
                    let badgeData = trilha.data_sugerida ? `<span class="badge bg-warning text-dark me-2"><i class="bi bi-calendar-event me-1"></i>${new Date(trilha.data_sugerida + 'T00:00:00').toLocaleDateString('pt-BR')}</span>` : '';

                    // Validação de Status (Trilha)
                    const trBase = db.trilhas.find(t => t.id == trilha.id);
                    const isTrilhaDesc = trBase && (trBase.status == 0 || trBase.status === false);
                    const badgeTrilha = isTrilhaDesc ? `<span class="badge bg-danger ms-2 px-2 py-1" style="font-size: 0.65rem;" title="Esta trilha foi inativada no sistema."><i class="bi bi-exclamation-triangle-fill"></i> Descontinuado</span>` : '';

                    return `
                            <div class="tree-node border rounded mb-3 bg-white shadow-sm" data-id="${idxT}">
                                <div class="tree-header" onclick="toggleTree(this)">
                                    <i class="bi bi-grip-vertical drag-handle fs-5"></i>
                                    <i class="bi bi-chevron-down me-2 text-primary"></i>
                                    <span class="badge badge-trilha me-2">TRILHA</span>
                                    <strong class="flex-grow-1 ${trilha.concluido ? 'item-concluido' : ''}">${trilha.titulo} ${badgeTrilha}</strong>
                                    ${badgeData}
                                    <span class="badge badge-tempo me-2"><i class="bi bi-clock me-1"></i>${formatarTempoVisual(tTri)}</span>
                                    <button type="button" class="btn btn-add-tree btn-outline-primary shadow-sm" onclick="abrirBusca('treino', ${idxT}, event)"><i class="bi bi-plus"></i> Treinamento</button>
                                    ${btnExcluirTrilha}
                                </div>
                                <div class="tree-content active p-2" id="treinos-container-${idxT}">
                                    ${(trilha.treinamentos || []).map((treino, idxTr) => {
                        const tTre = treino.tarefas ? treino.tarefas.reduce((s, t) => s + parseFloat(t.tempo_estimado || 0), 0) : 0;
                        const btnExcluirTreino = !treino.concluido ? `<button type="button" class="btn btn-sm text-danger ms-2 p-1" onclick="removerItem('treino', ${idxT}, event, ${idxTr})"><i class="bi bi-x-lg"></i></button>` : '<i class="bi bi-lock-fill text-muted ms-3"></i>';

                        // Validação de Status (Treinamento)
                        const treBase = db.treinamentos.find(t => t.id == treino.id);
                        const isTreinoDesc = treBase && (treBase.status == 0 || treBase.status === false);
                        const badgeTreino = isTreinoDesc ? `<span class="badge bg-danger ms-2 px-2 py-1" style="font-size: 0.65rem;" title="Este treinamento foi inativado no sistema."><i class="bi bi-exclamation-triangle-fill"></i> Descontinuado</span>` : '';

                        return `
                                        <div class="tree-item" data-id="${idxTr}">
                                            <div class="tree-header border-0 bg-transparent" onclick="toggleTree(this)">
                                                <i class="bi bi-grip-vertical drag-handle text-muted"></i>
                                                <i class="bi bi-chevron-down me-2 text-primary"></i>
                                                <span class="badge badge-treino me-2">Treinamento</span>
                                                <span class="flex-grow-1 ${treino.concluido ? 'item-concluido' : 'fw-bold text-dark'}">${treino.titulo} ${badgeTreino}</span>
                                                <span class="badge badge-tempo me-2"><i class="bi bi-clock me-1"></i>${formatarTempoVisual(tTre)}</span>
                                                <button type="button" class="btn btn-add-tree btn-outline-secondary shadow-sm" onclick="abrirBusca('tarefa', ${idxT}, event, ${idxTr})"><i class="bi bi-plus"></i> Tarefa</button>
                                                ${btnExcluirTreino}
                                            </div>
                                            <div class="tree-content active ms-4" id="tarefas-container-${idxT}-${idxTr}">
                                                ${(treino.tarefas || []).map((tarefa, idxTa) => {

                            // Validação de Status (Tarefa)
                            const tBase = db.tarefas.find(t => t.id == tarefa.id);
                            const isDescontinuada = tBase && (tBase.status == 0 || tBase.status === false);
                            const badgeDesc = isDescontinuada ? `<span class="badge bg-danger ms-2 px-2 py-1" style="font-size: 0.65rem;" title="Esta tarefa foi inativada no sistema."><i class="bi bi-exclamation-triangle-fill"></i> Descontinuado</span>` : '';

                            const btnExcluirTarefa = !tarefa.concluido ? `<button type="button" class="btn btn-sm text-danger p-0 ms-2" onclick="removerItem('tarefa', ${idxT}, event, ${idxTr}, ${idxTa})"><i class="bi bi-dash-circle"></i></button>` : '<i class="bi bi-lock-fill text-muted ms-2"></i>';
                            return `
                                                    <div class="tree-item py-1 d-flex justify-content-between pe-3 border-bottom border-light align-items-center" data-id="${idxTa}">
                                                        <div class="d-flex align-items-center flex-grow-1">
                                                            <i class="bi bi-grip-vertical drag-handle text-muted small me-2"></i>
                                                            <span class="${tarefa.concluido ? 'item-concluido' : 'text-secondary'}"><i class="bi ${tarefa.concluido ? 'bi-check-circle-fill text-success' : 'bi-circle'} me-2"></i>${tarefa.titulo} ${badgeDesc}</span>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge badge-tempo">${formatarTempoVisual(tarefa.tempo_estimado)}</span>
                                                            ${btnExcluirTarefa}
                                                        </div>
                                                    </div>`;
                        }).join("")}
                                            </div>
                                        </div>`;
                    }).join("")}
                                </div>
                            </div>`;
                }).join("");

                Sortable.create(document.getElementById('treeViewContainer'), {
                    handle: '.drag-handle', animation: 150, ghostClass: 'sortable-ghost',
                    onEnd: function (evt) { reordenarArray(planoEdicao.trilhas, evt.oldIndex, evt.newIndex); }
                });

                planoEdicao.trilhas.forEach((trilha, idxT) => {
                    if (trilha.treinamentos) {
                        Sortable.create(document.getElementById(`treinos-container-${idxT}`), {
                            handle: '.drag-handle', animation: 150, ghostClass: 'sortable-ghost',
                            onEnd: function (evt) { reordenarArray(planoEdicao.trilhas[idxT].treinamentos, evt.oldIndex, evt.newIndex); }
                        });
                        trilha.treinamentos.forEach((treino, idxTr) => {
                            if (treino.tarefas) {
                                Sortable.create(document.getElementById(`tarefas-container-${idxT}-${idxTr}`), {
                                    handle: '.drag-handle', animation: 150, ghostClass: 'sortable-ghost',
                                    onEnd: function (evt) { reordenarArray(planoEdicao.trilhas[idxT].treinamentos[idxTr].tarefas, evt.oldIndex, evt.newIndex); }
                                });
                            }
                        });
                    }
                });
            }

            document.getElementById("tempoTotalGeral").innerText = formatarTempoVisual(progresso.total);
            document.getElementById("progressoGeralModal").innerText = progresso.percentual + "%";
        }

        function reordenarArray(array, oldIndex, newIndex) {
            if (newIndex >= array.length) {
                let k = newIndex - array.length + 1;
                while (k--) array.push(undefined);
            }
            array.splice(newIndex, 0, array.splice(oldIndex, 1)[0]);
            renderTreeView();
        }

        function toggleTree(el) {
            const content = el.nextElementSibling;
            const icon = el.querySelector("i.bi-chevron-down, i.bi-chevron-right");
            if (content && content.classList.contains("tree-content")) {
                content.classList.toggle("active");
                icon.classList.toggle("bi-chevron-down");
                icon.classList.toggle("bi-chevron-right");
            }
        }

        // === LÓGICA DE BUSCA E ADIÇÃO ===
        function abrirBusca(tipo, idxT = null, event = null, idxTr = null) {
            if (event) event.stopPropagation();
            contextoAdicao = { tipo, idxT, idxTr };

            document.getElementById("tituloBusca").innerText = "Adicionar " + (tipo === 'trilha' ? 'Trilha' : (tipo === 'treino' ? 'Treinamento' : 'Tarefa'));
            document.getElementById("filtroItemBusca").value = "";

            const containerData = document.getElementById("containerDataSugerida");
            const inputData = document.getElementById("dataSugeridaTrilha");
            if (tipo === 'trilha') {
                containerData.classList.remove('d-none');
                inputData.value = "";
            } else {
                containerData.classList.add('d-none');
            }

            if (tipo === 'trilha') itensExibidosBusca = db.trilhas.filter(t => t.status == 1);
            else if (tipo === 'treino') itensExibidosBusca = db.treinamentos.filter(t => t.status == 1);
            else if (tipo === 'tarefa') itensExibidosBusca = db.tarefas.filter(t => t.status == 1);

            renderListaBusca(itensExibidosBusca);
            modalBusca.show();
        }

        function renderListaBusca(itens) {
            document.getElementById("listaBuscaItens").innerHTML = itens.map(i => `
                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3" onclick="confirmarAdicao(${i.id})">
                            <span class="fw-bold text-dark">${i.titulo}</span> 
                            <span class="badge bg-primary rounded-pill"><i class="bi bi-plus fs-6"></i> Adicionar</span>
                        </button>`).join("") || `<div class="p-4 text-center text-muted">Nenhum item ativo encontrado.</div>`;
        }

        function filtrarListaBusca() {
            const termo = document.getElementById("filtroItemBusca").value.toLowerCase();
            renderListaBusca(itensExibidosBusca.filter(i => i.titulo.toLowerCase().includes(termo)));
        }

        function confirmarAdicao(id) {
            if (contextoAdicao.tipo === 'trilha') {
                const dataSugerida = document.getElementById("dataSugeridaTrilha").value;
                if (!dataSugerida) {
                    Swal.fire({ icon: 'warning', title: 'Atenção', text: 'Você precisa definir uma Data Sugerida para concluir a Trilha.' });
                    return;
                }

                const clone = JSON.parse(JSON.stringify(db.trilhas.find(t => t.id == id)));
                clone.data_sugerida = dataSugerida;

                // 1. Filtra apenas os treinamentos vinculados que estão ATIVOS
                const treinosAtivos = (clone.treinamentos || []).filter(tid => {
                    const t = db.treinamentos.find(x => x.id == tid);
                    return t && (t.status == 1 || t.status === true);
                });

                clone.treinamentos = treinosAtivos.map(tid => {
                    const tr = JSON.parse(JSON.stringify(db.treinamentos.find(x => x.id == tid)));

                    // 2. Filtra apenas as tarefas vinculadas que estão ATIVAS
                    const tarefasAtivas = (tr.tarefas || []).filter(taid => {
                        const ta = db.tarefas.find(y => y.id == taid);
                        return ta && (ta.status == 1 || ta.status === true);
                    });

                    tr.tarefas = tarefasAtivas.map(taid => {
                        const tBase = db.tarefas.find(y => y.id == taid);
                        return { ...tBase, concluido: false };
                    });

                    return { ...tr, concluido: false };
                });

                planoEdicao.trilhas.push({ ...clone, concluido: false });
            }
            else if (contextoAdicao.tipo === 'treino') {
                const clone = JSON.parse(JSON.stringify(db.treinamentos.find(t => t.id == id)));

                // 1. Filtra apenas as tarefas vinculadas que estão ATIVAS
                const tarefasAtivas = (clone.tarefas || []).filter(taid => {
                    const ta = db.tarefas.find(y => y.id == taid);
                    return ta && (ta.status == 1 || ta.status === true);
                });

                clone.tarefas = tarefasAtivas.map(taid => {
                    const tBase = db.tarefas.find(y => y.id == taid);
                    return { ...tBase, concluido: false };
                });

                planoEdicao.trilhas[contextoAdicao.idxT].treinamentos.push({ ...clone, concluido: false });
            }
            else if (contextoAdicao.tipo === 'tarefa') {
                // A busca da modal já filtra por tarefas ativas, então é só adicionar
                const tBase = db.tarefas.find(t => t.id == id);
                planoEdicao.trilhas[contextoAdicao.idxT].treinamentos[contextoAdicao.idxTr].tarefas.push({ ...tBase, concluido: false });
            }

            renderTreeView();
            modalBusca.hide();
        }

        function removerItem(tipo, idxT, event, idxTr = null, idxTa = null) {
            event.stopPropagation();

            Swal.fire({
                title: 'Remover Item?',
                text: "Esta ação removerá este item do plano.",
                icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545',
                confirmButtonText: 'Sim, remover!'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (tipo === 'trilha') planoEdicao.trilhas.splice(idxT, 1);
                    if (tipo === 'treino') planoEdicao.trilhas[idxT].treinamentos.splice(idxTr, 1);
                    if (tipo === 'tarefa') planoEdicao.trilhas[idxT].treinamentos[idxTr].tarefas.splice(idxTa, 1);
                    renderTreeView();
                }
            });
        }

        // === RENDER GRID E VISUALIZAÇÃO ===
        function renderGrid(dados = listaPlanos) {
            document.getElementById("gridPlanos").innerHTML = dados.map(p => {
                const nomeAluno = p.aluno ? p.aluno.nome : 'Aluno não encontrado';
                let supervisoresNomes = 'Nenhum supervisor';

                if (p.supervisores_ids && p.supervisores_ids.length > 0) {
                    const supervisoresValidos = p.supervisores_ids
                        .map(id => listaSupervisores.find(s => s.id == id))
                        .filter(sup => sup !== undefined);

                    if (supervisoresValidos.length > 0) {
                        supervisoresNomes = supervisoresValidos.map(s => s.nome).join(', ');
                    }
                }

                const dataCriacao = new Date(p.created_at).toLocaleDateString('pt-BR');

                return `
                        <div class="col">
                            <div class="card h-100 shadow-sm card-plano border-0 border-top border-4 ${p.progresso === 100 ? 'border-success' : 'border-primary'}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="badge ${p.progresso === 100 ? 'bg-success' : 'bg-primary-subtle text-primary border border-primary-subtle px-3 py-2'}">${p.progresso === 100 ? 'Concluído' : 'Em Andamento'}</span>
                                        <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>${dataCriacao}</small>
                                    </div>
                                    <h5 class="fw-bold mb-1 text-dark">${p.titulo}</h5>
                                    <p class="text-muted small mb-1"><i class="bi bi-person-circle me-1"></i><strong>Aluno:</strong> ${nomeAluno}</p>
                                    <p class="text-muted small mb-3 text-truncate" title="${supervisoresNomes}"><i class="bi bi-eye me-1"></i><strong>Supervisores:</strong> ${supervisoresNomes}</p>

                                    <div class="d-flex justify-content-between small mb-1 mt-4">
                                        <span class="text-muted fw-bold">Progresso</span>
                                        <span class="fw-bold ${p.progresso === 100 ? 'text-success' : 'text-primary'}">${p.progresso}%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar ${p.progresso === 100 ? 'bg-success' : 'bg-primary'}" style="width: ${p.progresso}%"></div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0 pb-3 pt-0 d-flex gap-2">
                                    <button class="btn btn-light border fw-bold px-3" onclick="visualizarPlano(${p.id})"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-outline-primary fw-bold w-100" onclick="editarPlano(${p.id})"><i class="bi bi-pencil me-1"></i> Editar Plano</button>

                                    <form action="/admin/planos/${p.id}" method="POST" class="d-inline form-delete">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-outline-danger btn-delete px-3"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>`;
            }).join("") || '<div class="col-12 py-5 text-center text-muted py-5 bg-white rounded border shadow-sm"><i class="bi bi-person-workspace fs-1 text-muted mb-3"></i><br>Nenhum plano encontrado.</div>';

            document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function (e) {
                    const form = this.closest('form');
                    Swal.fire({
                        title: 'Excluir Plano?',
                        text: 'Deseja realmente remover este plano? O aluno perderá o acesso.',
                        icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Sim, excluir!'
                    }).then((result) => { if (result.isConfirmed) form.submit(); });
                });
            });
        }

        function visualizarPlano(id) {
            const p = listaPlanos.find(x => x.id == id);
            if (!p) return;

            const nomeAluno = p.aluno ? p.aluno.nome : 'Desconhecido';
            document.getElementById("visTitulo").innerText = p.titulo;
            document.getElementById("visAluno").innerText = nomeAluno;
            document.getElementById("visPercent").innerText = p.progresso + "%";
            document.getElementById("visProgressContainer").innerHTML = `<div class="progress-bar ${p.progresso === 100 ? 'bg-success' : 'bg-primary'}" style="width: ${p.progresso}%"></div>`;

            const estrutura = p.estrutura || { trilhas: [] };

            // Calculamos o progresso total para exibir na modal
            const progressoCalc = calcularProgressoTempo(estrutura);
            document.getElementById("visCargaHoraria").innerHTML = `<i class="bi bi-clock me-1"></i>${formatarTempoVisual(progressoCalc.total)}`;

            const container = document.getElementById("treeViewContainerVis");

            container.innerHTML = estrutura.trilhas.map((trilha) => {
                let badgeDataVis = '';
                if (trilha.data_sugerida) {
                    const dataFormatada = new Date(trilha.data_sugerida + 'T00:00:00').toLocaleDateString('pt-BR');
                    badgeDataVis = `<span class="badge bg-warning text-dark ms-2" title="Prazo de Conclusão"><i class="bi bi-calendar-event me-1"></i>${dataFormatada}</span>`;
                }

                // Validação de Status (Trilha na Modal de Visualização)
                const trBase = db.trilhas.find(t => t.id == trilha.id);
                const isTrilhaDesc = trBase && (trBase.status == 0 || trBase.status === false);
                const badgeTrilhaVis = isTrilhaDesc ? `<span class="badge bg-danger ms-2 px-2 py-1" style="font-size: 0.65rem;" title="Esta trilha foi inativada no sistema."><i class="bi bi-exclamation-triangle-fill"></i> Descontinuado</span>` : '';

                return `
                        <div class="tree-node border rounded mb-2 bg-light shadow-sm">
                            <div class="tree-header" onclick="toggleTree(this)">
                                <i class="bi bi-chevron-down me-2"></i>
                                <span class="badge badge-trilha me-2">TRILHA</span>
                                <strong class="flex-grow-1 ${trilha.concluido ? 'text-success' : ''}">${trilha.titulo} ${badgeTrilhaVis}</strong>
                                ${badgeDataVis}
                                ${trilha.concluido ? '<i class="bi bi-check-circle-fill text-success ms-2"></i>' : ''}
                            </div>
                            <div class="tree-content active p-2 ms-3">
                                ${(trilha.treinamentos || []).map((treino) => {

                    // Validação de Status (Treinamento na Modal de Visualização)
                    const treBase = db.treinamentos.find(t => t.id == treino.id);
                    const isTreinoDesc = treBase && (treBase.status == 0 || treBase.status === false);
                    const badgeTreinoVis = isTreinoDesc ? `<span class="badge bg-danger ms-2 px-2 py-1" style="font-size: 0.65rem;" title="Este treinamento foi inativado no sistema."><i class="bi bi-exclamation-triangle-fill"></i> Descontinuado</span>` : '';

                    return `
                                    <div class="tree-item">
                                        <div class="tree-header" onclick="toggleTree(this)">
                                            <i class="bi bi-chevron-down me-2"></i>
                                            <span class="badge badge-treino me-2">TREINAMENTO</span>
                                            <span class="flex-grow-1 ${treino.concluido ? 'text-success fw-bold' : ''}">${treino.titulo} ${badgeTreinoVis}</span>
                                            ${treino.concluido ? '<i class="bi bi-check-circle-fill text-success ms-2"></i>' : ''}
                                        </div>
                                        <div class="tree-content active ms-3">
                                            ${(treino.tarefas || []).map((tarefa) => {

                        // Validação de Status (Tarefa na Modal de Visualização)
                        const tBase = db.tarefas.find(t => t.id == tarefa.id);
                        const isDescontinuada = tBase && (tBase.status == 0 || tBase.status === false);
                        const badgeDesc = isDescontinuada ? `<span class="badge bg-danger ms-2 px-2 py-1" style="font-size: 0.65rem;" title="Esta tarefa foi inativada no sistema."><i class="bi bi-exclamation-triangle-fill"></i> Descontinuado</span>` : '';

                        return `
                                                <div class="py-1 small d-flex justify-content-between pe-3 border-bottom border-light">
                                                    <span class="${tarefa.concluido ? 'text-success' : 'text-muted'}">
                                                        <i class="bi ${tarefa.concluido ? 'bi-check-lg' : 'bi-circle'} me-1"></i> ${tarefa.titulo} ${badgeDesc}
                                                    </span>
                                                    <span class="badge badge-tempo">${formatarTempoVisual(tarefa.tempo_estimado)}</span>
                                                </div>
                                                `;
                    }).join("")}
                                        </div>
                                    </div>
                                `}).join("")}
                            </div>
                        </div>`;
            }).join("") || '<div class="text-center text-muted p-3">Nenhum conteúdo.</div>';

            bootstrapModalVis.show();
        }
    </script>
@endpush