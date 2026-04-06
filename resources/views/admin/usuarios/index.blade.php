@extends('layout.app')

@section('titulo', 'Gestão de Usuários')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark">Usuários</h2>
                <p class="text-muted mb-0">Gerenciamento de acessos e perfis do sistema SkillStep.</p>
            </div>
            <button type="button" class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal"
                data-bs-target="#modalNovoUsuario">
                <i class="bi bi-person-plus-fill me-2"></i>Novo Usuário
            </button>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('usuarios.index') }}" method="GET" class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" id="searchInput" class="form-control bg-light border-0"
                                placeholder="Buscar por nome, e-mail ou CPF..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="tipo" class="form-select bg-light border-0">
                            <option value="">Todos os perfis</option>
                            <option value="admin" {{ request('tipo') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="supervisor" {{ request('tipo') == 'supervisor' ? 'selected' : '' }}>Supervisor
                            </option>
                            <option value="aluno" {{ request('tipo') == 'aluno' ? 'selected' : '' }}>Aluno</option>
                        </select>
                    </div>
                    <!-- <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
                    </div> -->
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 35%">Usuário</th>
                            <th style="width: 20%">CPF</th>
                            <th style="width: 25%">Perfil</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usuarios as $usuario)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3">
                                            {{ strtoupper(substr($usuario->nome, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $usuario->nome }}</div>
                                            <div class="text-muted small">{{ $usuario->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted small">
                                    {{ $usuario->cpf }} {{-- Certifique-se de formatar no Model ou Controller se desejar pontos
                                    --}}
                                </td>
                                <td>
                                    @php
                                        $color = [
                                            'admin' => 'danger',
                                            'supervisor' => 'primary',
                                            'aluno' => 'success'
                                        ][$usuario->tipo_usuario] ?? 'secondary';
                                    @endphp
                                    <span
                                        class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle px-3 py-2 text-uppercase"
                                        style="font-size: 0.7rem;">
                                        {{ $usuario->tipo_usuario }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal"
                                            data-bs-target="#modalEditarUsuario" data-id="{{ $usuario->id }}"
                                            data-nome="{{ $usuario->nome }}" data-email="{{ $usuario->email }}"
                                            data-cpf="{{ $usuario->cpf }}" data-tipo="{{ $usuario->tipo_usuario }}"
                                            data-url="{{ route('usuarios.update', $usuario->id) }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        @if($usuario->id !== Auth::id())
                                            <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST"
                                                class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">Nenhum usuário encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top">
                {{ $usuarios->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL NOVO USUÁRIO --}}
    <div class="modal fade" id="modalNovoUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i>Novo Usuário</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('usuarios.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Nome Completo <span
                                        class="text-danger">*</span></label></label>
                                <input type="text" name="nome" class="form-control" required required maxlength="100" placeholder="Ex: Igor Schons">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">CPF <span
                                        class="text-danger">*</span></label></label>
                                <input type="text" name="cpf" id="cpf_novo" class="form-control cpf-mask" required
                                    pattern="\d{3}\.\d{3}\.\d{3}-\d{2}" placeholder="000.000.000-00"
                                    title="Digite o CPF completo no formato 000.000.000-00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Perfil <span
                                        class="text-danger">*</span></label>
                                <select name="tipo_usuario" class="form-select" required>
                                    <option value="aluno">Aluno</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">E-mail<span
                                        class="text-danger">*</span></label></label>
                                <input type="email" name="email" class="form-control" required
                                    placeholder="email@exemplo.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Senha Inicial <span
                                        class="text-danger">*</span></label></label>
                                <input type="password" name="senha" class="form-control" required minlength="6"
                                    placeholder="Mínimo 6 caracteres"
                                    oninvalid="this.setCustomValidity('A senha deve ter pelo menos 6 caracteres')"
                                    oninput="this.setCustomValidity('')">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Salvar Usuário</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDITAR USUÁRIO --}}
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i> Editar Usuário</h5>
                    <button type="button" class=" btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarUsuario" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Nome Completo</label>
                                <input type="text" id="edit_nome" name="nome" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">CPF</label>
                                <input type="text" id="edit_cpf" name="cpf" class="form-control cpf-mask" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Perfil</label>
                                <select id="edit_tipo" name="tipo_usuario" class="form-select" required>
                                    <option value="aluno">Aluno</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">E-mail</label>
                                <input type="email" id="edit_email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Nova Senha (Opcional)</label>
                                <input type="password" name="senha" class="form-control"
                                    placeholder="Deixe em branco para manter">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    {{-- Script do Vanilla Masker --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-masker/1.2.0/vanilla-masker.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // --- Tratamento de Erros (Ex: Usuário já existente) ---
            @if($errors->any())
                // Mantém a modal aberta
                const modalNovoUsuario = new bootstrap.Modal(document.getElementById('modalNovoUsuario'));
                modalNovoUsuario.show();

                // Dispara o alerta com o erro do Laravel (ex: CPF já cadastrado)
                Swal.fire({
                    title: 'Atenção!',
                    text: '{{ $errors->first() }}',
                    icon: 'warning',
                    confirmButtonColor: '#e85d2f'
                });
            @endif

            // --- Aplicação da Máscara de CPF ---
            const cpfInputs = document.querySelectorAll('.cpf-mask');
            cpfInputs.forEach(input => {
                VMasker(input).maskPattern('999.999.999-99');
            });

            // --- Lógica de Pesquisa em Tempo Real ---
            const searchInput = document.getElementById('searchInput');
            const searchForm = searchInput.closest('form');
            const typeSelect = searchForm.querySelector('select[name="tipo"]');
            let timeout = null;

            searchInput.addEventListener('keyup', function () {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    searchForm.submit();
                }, 500);
            });

            // Submissão automática do filtro de tipo, sem botão
            if (typeSelect) {
                typeSelect.addEventListener('change', function () {
                    searchForm.submit();
                });
            }

            // Foco no input de busca ao carregar
            if (searchInput.value !== "") {
                const val = searchInput.value;
                searchInput.value = '';
                searchInput.value = val;
                searchInput.focus();
            }

            // --- Lógica do Modal de Edição ---
            const editButtons = document.querySelectorAll('[data-bs-target="#modalEditarUsuario"]');
            const form = document.getElementById('formEditarUsuario');

            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const nome = this.getAttribute('data-nome');
                    const email = this.getAttribute('data-email');
                    const cpf = this.getAttribute('data-cpf');
                    const tipo = this.getAttribute('data-tipo');
                    const url = this.getAttribute('data-url');

                    form.action = url;
                    document.getElementById('edit_nome').value = nome;
                    document.getElementById('edit_email').value = email;
                    document.getElementById('edit_cpf').value = cpf;
                    document.getElementById('edit_tipo').value = tipo;

                    // Re-aplicar a máscara após preencher o valor via JS
                    VMasker(document.getElementById('edit_cpf')).maskPattern('999.999.999-99');
                });
            });

            // --- Lógica de Exclusão com SweetAlert2 ---
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    const form = this.closest('form');
                    const userName = this.closest('tr').querySelector('.fw-bold').innerText;

                    Swal.fire({
                        title: 'Excluir Usuário?',
                        text: `Deseja realmente remover ${userName}?`,
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