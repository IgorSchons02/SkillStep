@extends('layout.gestor')

@section('titulo', 'Usuários')

@section('conteudo')
    <div class="container-fluid">
        {{-- Cabeçalho da Página --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Gestão de Usuários</h2>
                <p class="text-muted">Gerencie os colaboradores e gestores de área do sistema.</p>
            </div>
            <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#modalNovoUsuario">
                <i class="bi bi-person-plus-fill me-2"></i>Novo Usuário
            </button>
        </div>
        {{-- Exibição de Alertas e Erros de Validação --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-octagon-fill me-2"></i> <strong>Atenção!</strong> Não foi possível salvar:
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        {{-- Área de Filtros --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <form action="{{ route('usuarios.index') }}" method="GET" class="row g-2">
                    <div class="{{ session('codigo_area') == null ? 'col-md-6' : 'col-md-10' }}">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-0"
                                placeholder="Pesquisar por nome ou e-mail..." value="{{ request('search') }}">
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
        </div>

        {{-- Tabela de Usuários --}}
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Nome do Usuário</th>
                                <th>E-mail</th>
                                <th>Perfil</th>
                                <th>Área</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $usuario)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="width: 40px; height: 40px;">
                                                <span class="fw-bold">{{ strtoupper(substr($usuario->nome, 0, 1)) }}</span>
                                            </div>
                                            <div class="fw-bold text-dark">{{ $usuario->nome }}</div>
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ $usuario->email }}</td>
                                    <td>
                                        @if($usuario->isGestor())
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i
                                                    class="bi bi-shield-lock me-1"></i> Gestor</span>
                                        @elseif($usuario->isAdmin())
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i
                                                    class="bi bi-star-fill me-1"></i> Admin</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><i
                                                    class="bi bi-person me-1"></i> Colaborador</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $usuario->area->name ?? 'Geral/Sem Área' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#modalEditarUsuario" data-id="{{ $usuario->id }}"
                                                data-nome="{{ $usuario->nome }}" data-email="{{ $usuario->email }}"
                                                data-tipo="{{ $usuario->codigo_tipo }}" data-area="{{ $usuario->codigo_area }}"
                                                data-url="{{ route('usuarios.update', $usuario->id) }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Tem certeza que deseja excluir o usuário: {{ $usuario->nome }}?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-people fs-1 d-block mb-3"></i>
                                        Nenhum usuário encontrado na sua área.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-center mt-2">
                    {{ $usuarios->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
    {{-- Modal de Novo Usuário --}}
    <div class="modal fade" id="modalNovoUsuario" tabindex="-1" aria-labelledby="modalNovoUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalNovoUsuarioLabel">
                        <i class="bi bi-person-plus text-primary me-2"></i>Cadastrar Usuário
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('usuarios.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">

                        {{-- Nome --}}
                        <div class="mb-3">
                            <label for="nome" class="form-label fw-bold">Nome Completo <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" required
                                placeholder="Ex: João da Silva">
                        </div>

                        {{-- E-mail --}}
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">E-mail de Acesso <span
                                    class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required
                                placeholder="joao@empresa.com">
                        </div>

                        {{-- Senha --}}
                        <div class="mb-3">
                            <label for="senha" class="form-label fw-bold">Senha Inicial <span
                                    class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="senha" name="senha" required minlength="6"
                                placeholder="Mínimo de 6 caracteres">
                        </div>

                        <div class="row">
                            {{-- Tipo de Perfil --}}
                            <div class="col-md-6 mb-3">
                                <label for="codigo_tipo" class="form-label fw-bold">Perfil de Acesso <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="codigo_tipo" name="codigo_tipo" required>
                                    <option value="">Selecione...</option>
                                    @foreach($tipos as $tipo)
                                        <option value="{{ $tipo->id }}">{{ ucfirst($tipo->descricao) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Área (Oculto por padrão, aparece se for Gestor) --}}
                            <div class="col-md-6 mb-3" id="div_area_container" style="display: none;">
                                <label for="codigo_area" class="form-label fw-bold">Área Gerenciada <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="codigo_area" name="codigo_area">
                                    <option value="">Selecione a área...</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Salvar Usuário</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal de Edição de Usuário --}}
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalEditarUsuarioLabel">
                        <i class="bi bi-pencil-square text-primary me-2"></i>Editar Usuário
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formEditarUsuario" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">

                        {{-- Nome --}}
                        <div class="mb-3">
                            <label for="edit_nome" class="form-label fw-bold">Nome Completo <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_nome" name="nome" required>
                        </div>

                        {{-- E-mail --}}
                        <div class="mb-3">
                            <label for="edit_email" class="form-label fw-bold">E-mail de Acesso <span
                                    class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>

                        {{-- Senha (Opcional) --}}
                        <div class="mb-3">
                            <label for="edit_senha" class="form-label fw-bold">Nova Senha <span
                                    class="text-muted fw-normal">(opcional)</span></label>
                            <input type="password" class="form-control" id="edit_senha" name="senha" minlength="6"
                                placeholder="Deixe em branco para manter a senha atual">
                            <small class="text-muted">Preencha apenas se desejar redefinir o acesso deste usuário.</small>
                        </div>

                        <div class="row">
                            {{-- Tipo de Perfil --}}
                            <div class="col-md-6 mb-3">
                                <label for="edit_codigo_tipo" class="form-label fw-bold">Perfil de Acesso <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="edit_codigo_tipo" name="codigo_tipo" required>
                                    <option value="">Selecione...</option>
                                    @foreach($tipos as $tipo)
                                        <option value="{{ $tipo->id }}">{{ ucfirst($tipo->descricao) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Área --}}
                            <div class="col-md-6 mb-3" id="edit_div_area_container" style="display: none;">
                                <label for="edit_codigo_area" class="form-label fw-bold">Área Gerenciada <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="edit_codigo_area" name="codigo_area">
                                    <option value="">Selecione a área...</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
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
            const selectTipo = document.getElementById('codigo_tipo');
            const divArea = document.getElementById('div_area_container');
            const selectArea = document.getElementById('codigo_area');

            if (selectTipo && divArea && selectArea) {
                selectTipo.addEventListener('change', function () {
                    // Se o ID selecionado for 1 (Gestor)
                    if (this.value === '1') {
                        divArea.style.display = 'block';
                        selectArea.setAttribute('required', 'required'); // Torna obrigatório
                    } else {
                        // Se for Colaborador (2) ou Admin (3)
                        divArea.style.display = 'none';
                        selectArea.removeAttribute('required'); // Remove a obrigatoriedade
                        selectArea.value = ''; // Limpa o valor
                    }
                });
            }

            // --- LÓGICA DO MODAL DE EDIÇÃO DE USUÁRIOS ---
        const modalEditar = document.getElementById('modalEditarUsuario');
        const editSelectTipo = document.getElementById('edit_codigo_tipo');
        const editDivArea = document.getElementById('edit_div_area_container');
        const editSelectArea = document.getElementById('edit_codigo_area');

        if (modalEditar) {
            // Lógica de mostrar/esconder a Área na Edição
            editSelectTipo.addEventListener('change', function() {
                if (this.value === '1') {
                    editDivArea.style.display = 'block';
                    editSelectArea.setAttribute('required', 'required');
                } else {
                    editDivArea.style.display = 'none';
                    editSelectArea.removeAttribute('required');
                    editSelectArea.value = ''; 
                }
            });

            // Lógica ao abrir o modal
            modalEditar.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                
                // Atualiza a URL do form (Action)
                document.getElementById('formEditarUsuario').action = button.getAttribute('data-url');
                
                // Preenche os campos de texto
                document.getElementById('edit_nome').value = button.getAttribute('data-nome');
                document.getElementById('edit_email').value = button.getAttribute('data-email');
                document.getElementById('edit_senha').value = ''; // Sempre limpa a senha por segurança
                
                // Preenche o perfil
                const tipoValue = button.getAttribute('data-tipo');
                editSelectTipo.value = tipoValue;
                
                // Preenche a área
                const areaValue = button.getAttribute('data-area');
                if (areaValue) {
                    editSelectArea.value = areaValue;
                } else {
                    editSelectArea.value = '';
                }

                // Dispara o evento 'change' manualmente para a caixinha da área aparecer/sumir do jeito certo
                editSelectTipo.dispatchEvent(new Event('change'));
            });
        }
        });
    </script>
@endsection