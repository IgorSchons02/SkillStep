@extends('layout.app')

@section('titulo', 'Meus Alunos - Acompanhamento')

@section('content')
    <div class="container-fluid">
        {{-- Cabeçalho --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark"><i class="bi bi-person-video3 text-primary me-2"></i>Meus Alunos</h2>
                <p class="text-muted mb-0">Acompanhe o progresso das jornadas de aprendizado dos seus alunos vinculados.</p>
            </div>
        </div>

        {{-- Filtros de Busca e Status --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form action="{{ route('meus-alunos.index') }}" method="GET" id="searchForm" class="row g-2">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" id="pesquisaPlanoGrid" class="form-control bg-light border-0"
                                placeholder="Pesquisar por nome do aluno ou título do plano..."
                                value="{{ request('search') }}" autocomplete="off" />
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

        {{-- Grid de Planos dos Alunos gerada pelo JavaScript --}}
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4" id="gridPlanos"></div>

        {{-- Paginação Nativa do Laravel --}}
        @if ($planos->hasPages())
            <div class="card-footer bg-white border-top px-4 pt-3 pb-1">
                {{ $planos->withQueryString()->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL VISUALIZAR ANDAMENTO --}}
    <div class="modal fade" id="modalVisualizar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-eye me-2"></i>Visualizar Andamento do Aluno</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="infoPlanoVis" class="mb-4">
                        <h4 class="fw-bold mb-1" id="visTitulo"></h4>
                        <p class="text-muted mb-0"><i class="bi bi-person me-1"></i> Aluno: <strong id="visAluno"
                                class="text-dark"></strong></p>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Progresso Geral</span>
                                <span class="fw-bold" id="visPercent">0%</span>
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
    <script>
        // Dados vindos do Laravel
        const db = @json($db);
        let listaPlanos = @json($planos->items());
        const listaSupervisores = @json($supervisores);

        const bootstrapModalVis = new bootstrap.Modal(document.getElementById('modalVisualizar'));

        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const idModalAuto = urlParams.get('abrir_modal');
            if (idModalAuto) {
                visualizarPlano(idModalAuto);
            }
            const searchForm = document.getElementById('searchForm');
            const inputPesquisa = document.getElementById('pesquisaPlanoGrid');
            const selectStatus = document.getElementById('filtroStatus');
            let timeout = null;

            // Filtro Real-time no Front-end + Submit para o Back-end
            inputPesquisa.addEventListener('keyup', function () {
                filtrarGridLocal(); // Filtra a tela instantaneamente
                clearTimeout(timeout);
                timeout = setTimeout(() => searchForm.submit(), 800); // Submete após parar de digitar
            });

            selectStatus.addEventListener('change', function () {
                filtrarGridLocal();
                searchForm.submit();
            });

            renderGrid();
        });

        // Função de Filtro Dinâmico no JavaScript
        function filtrarGridLocal() {
            const termo = document.getElementById('pesquisaPlanoGrid').value.toLowerCase();
            const status = document.getElementById('filtroStatus').value;

            const planosFiltrados = listaPlanos.filter(p => {
                const nomeAluno = p.aluno ? p.aluno.nome.toLowerCase() : '';
                const tituloPlano = p.titulo.toLowerCase();
                const matchTexto = nomeAluno.includes(termo) || tituloPlano.includes(termo);

                let matchStatus = true;
                if (status === 'andamento') matchStatus = p.progresso < 100;
                if (status === 'concluido') matchStatus = p.progresso === 100;

                return matchTexto && matchStatus;
            });

            renderGrid(planosFiltrados);
        }

        // Formatação de tempo para a visualização
        function formatarTempoVisual(horasDecimal) {
            if (!horasDecimal) return '0h';
            const h = Math.floor(horasDecimal);
            const m = Math.round((horasDecimal - h) * 60);
            if (h === 0) return `${m}m`;
            if (m === 0) return `${h}h`;
            return `${h}h ${m}m`;
        }

        // Interação de abrir/fechar a árvore na visualização
        function toggleTree(el) {
            const content = el.nextElementSibling;
            const icon = el.querySelector("i.bi-chevron-down, i.bi-chevron-right");
            if (content && content.classList.contains("tree-content")) {
                content.classList.toggle("active");
                icon.classList.toggle("bi-chevron-down");
                icon.classList.toggle("bi-chevron-right");
            }
        }

        // === RENDER GRID (Apenas Leitura) ===
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
                                        <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>Atribuído em ${dataCriacao}</small>
                                    </div>
                                    <h5 class="fw-bold mb-1 text-dark">${p.titulo}</h5>
                                    <p class="text-muted small mb-1"><i class="bi bi-person-circle me-1"></i><strong>Aluno:</strong> <span class="text-dark">${nomeAluno}</span></p>
                                    <p class="text-muted small mb-3 text-truncate" title="${supervisoresNomes}"><i class="bi bi-eye me-1"></i><strong>Supervisores:</strong> ${supervisoresNomes}</p>

                                    <div class="d-flex justify-content-between small mb-1 mt-4">
                                        <span class="text-muted fw-bold">Progresso do Aluno</span>
                                        <span class="fw-bold ${p.progresso === 100 ? 'text-success' : 'text-primary'}">${p.progresso}%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar ${p.progresso === 100 ? 'bg-success' : 'bg-primary'}" style="width: ${p.progresso}%"></div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0 pb-3 pt-0">
                                    <button class="btn btn-outline-primary fw-bold w-100" onclick="visualizarPlano(${p.id})">
                                        <i class="bi bi-graph-up me-2"></i> Ver Progresso
                                    </button>
                                </div>
                            </div>
                        </div>`;
            }).join("") || '<div class="col-12 py-5 text-center text-muted py-5 bg-white rounded border shadow-sm"><i class="bi bi-person-video3 fs-1 text-muted mb-3"></i><br>Nenhum aluno ou plano corresponde à sua busca.</div>';
        }

        // === VISUALIZAR PLANO ===
        function visualizarPlano(id) {
            const p = listaPlanos.find(x => x.id == id);
            if (!p) return;

            const nomeAluno = p.aluno ? p.aluno.nome : 'Desconhecido';
            document.getElementById("visTitulo").innerText = p.titulo;
            document.getElementById("visAluno").innerText = nomeAluno;
            document.getElementById("visPercent").innerText = p.progresso + "%";
            document.getElementById("visProgressContainer").innerHTML = `<div class="progress-bar ${p.progresso === 100 ? 'bg-success' : 'bg-primary'}" style="width: ${p.progresso}%"></div>`;

            const container = document.getElementById("treeViewContainerVis");
            const estrutura = p.estrutura || { trilhas: [] };

            container.innerHTML = estrutura.trilhas.map((trilha) => {
                let badgeDataVis = '';
                if (trilha.data_sugerida) {
                    const dataFormatada = new Date(trilha.data_sugerida + 'T00:00:00').toLocaleDateString('pt-BR');
                    badgeDataVis = `<span class="badge bg-warning text-dark ms-2" title="Prazo de Conclusão"><i class="bi bi-calendar-event me-1"></i>${dataFormatada}</span>`;
                }

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
            }).join("") || '<div class="text-center text-muted p-3">Nenhum conteúdo estrutural encontrado.</div>';

            bootstrapModalVis.show();
        }
    </script>
@endpush