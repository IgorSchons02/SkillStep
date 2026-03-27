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

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('usuarios.index') }}" method="GET" class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-0"
                                placeholder="Buscar por nome ou e-mail..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="tipo" class="form-select bg-light border-0">
                            <option value="">Todos os perfis</option>
                            <option value="admin" {{ request('tipo') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="supervisor" {{ request('tipo') == 'supervisor' ? 'selected' : '' }}>Supervisor
                            </option>
                            <option value="aluno" {{ request('tipo') == 'aluno' ? 'selected' : '' }}>Aluno</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 40%">Usuário</th>
                            <th style="width: 30%">Perfil</th>
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
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#modalEditarUsuario" data-id="{{ $usuario->id }}"
                                            data-nome="{{ $usuario->nome }}" data-email="{{ $usuario->email }}"
                                            data-tipo="{{ $usuario->tipo_usuario }}"
                                            data-url="{{ route('usuarios.update', $usuario->id) }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        @if($usuario->id !== Auth::id())
                                            <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Excluir {{ $usuario->nome }}?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">Nenhum usuário encontrado.</td>
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

    <div class="modal fade" id="modalNovoUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('usuarios.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome Completo</label>
                            <input type="text" name="nome" class="form-control" required placeholder="Ex: Igor Schons">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">E-mail</label>
                            <input type="email" name="email" class="form-control" required placeholder="email@exemplo.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Senha Inicial</label>
                            <input type="password" name="senha" class="form-control" required
                                placeholder="Mínimo 6 caracteres">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Perfil</label>
                            <select name="tipo_usuario" class="form-select" required>
                                <option value="aluno">Aluno</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="admin">Administrador</option>
                            </select>
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

    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-bold">Editar Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarUsuario" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome Completo</label>
                            <input type="text" id="edit_nome" name="nome" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">E-mail</label>
                            <input type="email" id="edit_email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Perfil</label>
                            <select id="edit_tipo" name="tipo_usuario" class="form-select" required>
                                <option value="aluno">Aluno</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nova Senha (Opcional)</label>
                            <input type="password" name="senha" class="form-control"
                                placeholder="Deixe em branco para manter">
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

@push('css')
    <style>
        .avatar-circle {
            width: 42px;
            height: 42px;
            background-color: var(--bg-dark);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #6c757d;
        }
    </style>
@endpush

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Pegamos todos os botões que abrem a modal de edição
            const editButtons = document.querySelectorAll('[data-bs-target="#modalEditarUsuario"]');
            const modalEdit = document.getElementById('modalEditarUsuario');
            const form = document.getElementById('formEditarUsuario');

            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    // Pegamos os dados do botão clicado
                    const id = this.getAttribute('data-id');
                    const nome = this.getAttribute('data-nome');
                    const email = this.getAttribute('data-email');
                    const tipo = this.getAttribute('data-tipo');
                    const url = this.getAttribute('data-url');

                    // Preenchemos os campos
                    form.action = url;
                    document.getElementById('edit_nome').value = nome;
                    document.getElementById('edit_email').value = email;
                    document.getElementById('edit_tipo_usuario').value = tipo;

                    console.log("Editando usuário: " + nome);
                });
            });
        });
    </script>
@endpush