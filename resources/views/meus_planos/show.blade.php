@extends('layout.app')

@section('titulo', 'Detalhes do Plano')

@push('css')
<style>
    :root { --primary-color: #0d6efd; --skill-purple: #6610f2; }
    .bg-gradient-skill { background: linear-gradient(45deg, #e85d2f, #d4491c); }
    .sticky-sidebar { position: sticky; top: 80px; }
    
    .tree-node { border-left: 4px solid #dee2e6; transition: 0.3s; background: #fff; }
    .tree-node.concluido { border-left-color: #198754; }
    
    .task-item { transition: all 0.2s; border-radius: 8px; border: 1px solid transparent; }
    .task-item:hover { background-color: #f8f9fa; }
    .task-item.done { background-color: #e8f5e933; }

    .tree-header, .treino-header { cursor: pointer; user-select: none; }
    .tree-content { display: none; }
    .tree-content.active { display: block; }

    .task-instructions { display: none; background: #fdfdfd; border-top: 1px solid #eee; }
    .task-instructions.active { display: block; }

    .badge-time { font-size: 0.75rem; background: #f1f3f5; color: #495057; border: 1px solid #dee2e6; }
    
    .check-box {
        width: 24px; height: 24px; border: 2px solid #dee2e6; border-radius: 6px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: 0.2s; cursor: pointer; background: white; flex-shrink: 0;
    }
    .check-box:hover { border-color: #e85d2f; }
    .task-item.done .check-box { background: #198754; border-color: #198754; color: white; }
    .task-item.done .task-title { color: #198754; text-decoration: line-through; opacity: 0.7; }

    .treino-header { transition: 0.2s; padding: 10px; border-radius: 5px; }
    .treino-header:hover { background: #f8f9fa; }

    .icon-info-task { transition: 0.2s; cursor: pointer; }
    .icon-info-task:hover { color: #e85d2f !important; transform: scale(1.1); }
</style>
@endpush

@section('content')
<div class="container-fluid pb-5">
    <div class="row g-4">
        {{-- COLUNA ESQUERDA: SIDEBAR COM ESTATÍSTICAS --}}
        <div class="col-lg-4">
            <div class="sticky-sidebar">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-1" id="planoTitulo">{{ $plano->titulo }}</h4>
                        <p class="text-muted small mb-4" id="planoData">Atribuído em {{ $plano->created_at->format('d/m/Y') }}</p>
                        
                        <div class="mb-3 d-flex justify-content-between align-items-end">
                            <label class="fw-bold small text-uppercase text-muted">Progresso Geral</label>
                            <h3 class="fw-bold text-primary mb-0" id="percentGeral">{{ $plano->progresso }}%</h3>
                        </div>
                        <div class="progress mb-4" style="height: 12px;">
                            <div id="barGeral" class="progress-bar bg-gradient-skill progress-bar-striped {{ $plano->progresso < 100 ? 'progress-bar-animated' : 'bg-success' }}" style="width: {{ $plano->progresso }}%"></div>
                        </div>

                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block">Tempo Total</small>
                                    <strong id="tempoTotal">0h</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block">Concluído</small>
                                    <strong id="tempoConcluido" class="text-success">0h</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <a href="{{ route('meus-planos.index') }}" class="btn btn-outline-secondary w-100 py-3 fw-bold shadow-sm">
                    <i class="bi bi-arrow-left me-2"></i> Voltar para Meus Planos
                </a>
            </div>
        </div>

        {{-- COLUNA DIREITA: A JORNADA (ÁRVORE) --}}
        <div class="col-lg-8">
            <h5 class="fw-bold mb-4 text-uppercase small" style="letter-spacing: 1px;"><i class="bi bi-map me-2 text-primary"></i>Sua Jornada de Aprendizado</h5>
            <div id="jornadaContainer">
                {{-- Renderizado via JS --}}
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('js')
<script>
    let planoAtivo = @json($plano);
    const tarefasBd = @json($tarefasBd); // Dicionário de descrições que veio do Controller
    
    if (!planoAtivo.estrutura) planoAtivo.estrutura = { trilhas: [] };

    let sessoesAbertas = new Set();

    if (planoAtivo.estrutura.trilhas.length > 0) {
        sessoesAbertas.add('tri-0');
        if (planoAtivo.estrutura.trilhas[0].treinamentos && planoAtivo.estrutura.trilhas[0].treinamentos.length > 0) {
            sessoesAbertas.add('tri-0-tre-0');
        }
    }

    // Identifica links na descrição e transforma em um botão bonito!
    function linkify(inputText) {
        if (!inputText) return 'Nenhuma descrição detalhada disponível.';
        const replacedText = inputText.replace(
            /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim,
            '<br><a href="$1" target="_blank" class="btn btn-sm btn-outline-primary mt-3 fw-bold"><i class="bi bi-box-arrow-up-right me-1"></i> Acessar Material / Link</a>'
        );
        return replacedText;
    }

    function formatarTempo(horasDecimal) {
        if (!horasDecimal) return '0m';
        const h = Math.floor(horasDecimal);
        const m = Math.round((horasDecimal - h) * 60);
        if (h === 0) return `${m}m`;
        if (m === 0) return `${h}h`;
        return `${h}h ${m}m`;
    }

    function calcularEstatisticasGlobais() {
        let totalHoras = 0, concluidoHoras = 0;
        
        if (planoAtivo.estrutura.trilhas) {
            planoAtivo.estrutura.trilhas.forEach(trilha => {
                if (trilha.treinamentos) {
                    trilha.treinamentos.forEach(treino => {
                        if (treino.tarefas) {
                            treino.tarefas.forEach(tarefa => {
                                const tempo = parseFloat(tarefa.tempo_estimado) || 0;
                                totalHoras += tempo;
                                if (tarefa.concluido) concluidoHoras += tempo;
                            });
                        }
                    });
                }
            });
        }

        const percent = totalHoras === 0 ? 0 : Math.round((concluidoHoras / totalHoras) * 100);
        
        document.getElementById("percentGeral").innerText = percent + "%";
        document.getElementById("barGeral").style.width = percent + "%";
        
        if (percent === 100) {
            document.getElementById("barGeral").classList.remove('progress-bar-animated', 'bg-gradient-skill');
            document.getElementById("barGeral").classList.add('bg-success');
        } else {
            document.getElementById("barGeral").classList.add('progress-bar-animated', 'bg-gradient-skill');
            document.getElementById("barGeral").classList.remove('bg-success');
        }

        document.getElementById("tempoTotal").innerText = formatarTempo(totalHoras);
        document.getElementById("tempoConcluido").innerText = formatarTempo(concluidoHoras);
    }

    function renderJornada() {
        const container = document.getElementById("jornadaContainer");
        
        if (!planoAtivo.estrutura.trilhas || planoAtivo.estrutura.trilhas.length === 0) {
            container.innerHTML = '<div class="text-center py-5 text-muted bg-white rounded border">Este plano de estudos ainda não possui conteúdo estruturado.</div>';
            return;
        }

        container.innerHTML = planoAtivo.estrutura.trilhas.map((trilha, idxTri) => {
            const treinamentos = trilha.treinamentos || [];
            let tempoTrilha = 0, tarefasTrilha = 0, concluidasTrilha = 0;
            
            treinamentos.forEach(tr => {
                const tarefas = tr.tarefas || [];
                tarefas.forEach(ta => {
                    tempoTrilha += parseFloat(ta.tempo_estimado || 0);
                    tarefasTrilha++;
                    if (ta.concluido) concluidasTrilha++;
                });
            });

            const trilha100Porcento = tarefasTrilha > 0 && tarefasTrilha === concluidasTrilha;
            const keyTrilha = `tri-${idxTri}`;
            const isActiveTri = sessoesAbertas.has(keyTrilha) ? 'active' : '';
            const iconTri = sessoesAbertas.has(keyTrilha) ? 'bi-chevron-down' : 'bi-chevron-right';

            let badgeData = '';
            if (trilha.data_sugerida) {
                badgeData = `<span class="badge bg-warning text-dark ms-2"><i class="bi bi-calendar-event me-1"></i>${new Date(trilha.data_sugerida + 'T00:00:00').toLocaleDateString('pt-BR')}</span>`;
            }

            return `
            <div class="tree-node shadow-sm mb-4 rounded-3 p-0 ${trilha100Porcento ? 'concluido border-success border-4' : 'border-4'}">
                <div class="tree-header p-3 d-flex align-items-center bg-white border-bottom rounded-top" onclick="toggleSection(this, '${keyTrilha}')">
                    <i class="bi ${iconTri} me-3 text-primary fs-5"></i>
                    <div class="flex-grow-1">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-1">TRILHA</span>
                        ${trilha100Porcento ? '<i class="bi bi-check-circle-fill text-success ms-2"></i>' : ''}
                        <h5 class="fw-bold mb-0 d-inline-block">${trilha.titulo}</h5>
                        ${badgeData}
                    </div>
                    <div class="text-end">
                        <span class="badge badge-time mb-1 d-block"><i class="bi bi-clock me-1"></i>${formatarTempo(tempoTrilha)}</span>
                        <small class="text-muted fw-bold">${concluidasTrilha}/${tarefasTrilha} Tarefas</small>
                    </div>
                </div>
                
                <div class="tree-content ${isActiveTri} p-3 bg-white rounded-bottom">
                    ${treinamentos.length === 0 ? '<p class="text-muted small mb-0">Nenhum treinamento nesta trilha.</p>' : treinamentos.map((treino, idxTre) => {
                        
                        const tarefas = treino.tarefas || [];
                        let tempoTreino = 0, concluidasTreino = 0;
                        tarefas.forEach(ta => {
                            tempoTreino += parseFloat(ta.tempo_estimado || 0);
                            if (ta.concluido) concluidasTreino++;
                        });
                        
                        const percentTreino = tarefas.length === 0 ? 0 : Math.round((concluidasTreino / tarefas.length) * 100);
                        const keyTreino = `tri-${idxTri}-tre-${idxTre}`;
                        const isActiveTre = sessoesAbertas.has(keyTreino) ? 'active' : '';
                        const iconTre = sessoesAbertas.has(keyTreino) ? 'bi-chevron-down' : 'bi-chevron-right';

                        return `
                        <div class="mb-4 border rounded-3 p-0 bg-light">
                            <div class="treino-header d-flex justify-content-between align-items-center bg-white border-bottom rounded-top" onclick="toggleSection(this, '${keyTreino}')">
                                <h6 class="fw-bold mb-0 d-flex align-items-center">
                                    <i class="bi ${iconTre} me-2 text-secondary"></i>
                                    <i class="bi bi-collection-play me-2 text-primary"></i>${treino.titulo}
                                </h6>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-dark border small">${formatarTempo(tempoTreino)}</span>
                                    <span class="badge ${percentTreino === 100 ? 'bg-success' : 'bg-secondary'}">${percentTreino}%</span>
                                </div>
                            </div>
                            
                            <div class="tree-content ${isActiveTre}">
                                <div class="progress m-0" style="height: 3px; border-radius: 0;">
                                    <div class="progress-bar ${percentTreino === 100 ? 'bg-success' : 'bg-primary'}" style="width: ${percentTreino}%"></div>
                                </div>
                                
                                <div class="list-group list-group-flush rounded-bottom">
                                ${tarefas.length === 0 ? '<div class="p-3 text-muted small">Nenhuma tarefa.</div>' : tarefas.map((tarefa, idxTa) => {
                                    
                                    const keyTask = `task-${idxTri}-${idxTre}-${idxTa}`;
                                    const isInstActive = sessoesAbertas.has(keyTask) ? 'active' : '';
                                    
                                    // Bate a Tarefa do JSON com o Banco de Dados
                                    const descBanco = tarefasBd[tarefa.id] ? tarefasBd[tarefa.id].descricao : null;
                                    
                                    // Processa a descrição transformando URLs em botões
                                    const descricaoComLinks = linkify(descBanco || tarefa.descricao);

                                    return `
                                    <div class="task-wrapper">
                                        <div class="list-group-item task-item d-flex align-items-center py-3 border-0 border-bottom ${tarefa.concluido ? 'done' : ''}">
                                            
                                            <div class="check-box me-3" onclick="toggleTarefa(${idxTri}, ${idxTre}, ${idxTa})">
                                                ${tarefa.concluido ? '<i class="bi bi-check-lg fw-bold"></i>' : ''}
                                            </div>
                                            
                                            <div class="flex-grow-1 task-title fw-medium d-flex align-items-center">
                                                ${tarefa.titulo} 
                                                <i class="bi bi-info-circle ms-2 text-muted fs-5 icon-info-task" title="Ver detalhes/links da tarefa" onclick="toggleInstructions(this, '${keyTask}')"></i>
                                            </div>
                                            
                                            <span class="badge bg-light text-secondary border small">${formatarTempo(tarefa.tempo_estimado)}</span>
                                        </div>
                                        
                                        <div class="task-instructions p-4 bg-white ${isInstActive}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <p class="small text-muted mb-0 pe-4 lh-base">${descricaoComLinks}</p>
                                            </div>
                                        </div>
                                    </div>
                                    `}).join("")}
                                </div>
                            </div>
                        </div>`;
                    }).join("")}
                </div>
            </div>`;
        }).join("");
    }

    function toggleInstructions(el, key) {
        const instructions = el.closest('.task-wrapper').querySelector('.task-instructions');
        instructions.classList.toggle('active');
        
        if(instructions.classList.contains('active')) sessoesAbertas.add(key);
        else sessoesAbertas.delete(key);
    }

    function toggleSection(el, key) {
        const content = el.nextElementSibling;
        const icon = el.querySelector("i.bi-chevron-down, i.bi-chevron-right");
        
        content.classList.toggle("active");
        
        if(content.classList.contains("active")) {
            sessoesAbertas.add(key);
            if (icon) { icon.classList.remove("bi-chevron-right"); icon.classList.add("bi-chevron-down"); }
        } else {
            sessoesAbertas.delete(key);
            if (icon) { icon.classList.remove("bi-chevron-down"); icon.classList.add("bi-chevron-right"); }
        }
    }

    // Ação principal do Aluno: Marcar/Desmarcar tarefa e salvar no banco
    function toggleTarefa(idxTri, idxTre, idxTa) {
        planoAtivo.estrutura.trilhas[idxTri].treinamentos[idxTre].tarefas[idxTa].concluido = !planoAtivo.estrutura.trilhas[idxTri].treinamentos[idxTre].tarefas[idxTa].concluido;
        
        calcularEstatisticasGlobais();
        renderJornada();
        salvarProgressoNoBanco();
    }

    function salvarProgressoNoBanco() {
        // Função pronta aguardando o Backend!
        fetch(`/meus-planos/${planoAtivo.id}/progresso`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                estrutura: planoAtivo.estrutura
            })
        })
        .then(response => response.json())
        .then(data => {
            if(!data.success) {
                console.error("Erro ao salvar progresso", data);
            }
        })
        .catch(error => console.error("Erro de conexão", error));
    }

    // Inicialização da Tela
    document.addEventListener('DOMContentLoaded', function() {
        calcularEstatisticasGlobais();
        renderJornada();
    });
</script>
@endpush