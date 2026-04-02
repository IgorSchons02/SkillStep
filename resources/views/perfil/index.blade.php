@extends('layout.app')

@section('titulo', 'Meu Perfil')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            {{-- CARD DE CABEÇALHO DO PERFIL --}}
            <div class="card card-custom mb-4 overflow-hidden">
                <div class="profile-header"></div>
                <div class="avatar-wrapper d-flex align-items-end justify-content-between mb-4">
                    {{-- Puxa a primeira letra do nome do usuário logado --}}
                    <div class="avatar-main" id="profileInitial">{{ substr(Auth::user()->nome, 0, 1) }}</div>
                    <div class="pb-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill text-uppercase">
                            <i class="bi bi-shield-check me-1"></i> Perfil: {{ Auth::user()->tipo_usuario }}
                        </span>
                    </div>
                </div>
                <div class="px-4 pb-4">
                    <h2 class="fw-bold mb-0" id="profileNameDisplay">{{ Auth::user()->nome }}</h2>
                    <p class="text-muted"><i class="bi bi-envelope me-2"></i>{{ Auth::user()->email }}</p>
                </div>
            </div>

            <div class="row">
                {{-- MENU LATERAL --}}
                <div class="col-md-4 mb-4">
                    <div class="card card-custom p-3">
                        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist">
                            <button class="nav-link active text-start py-3" data-bs-toggle="pill" data-bs-target="#tab-dados" type="button">
                                <i class="bi bi-person-lines-fill me-2"></i> Meus Dados
                            </button>
                            <button class="nav-link text-start py-3" data-bs-toggle="pill" data-bs-target="#tab-seguranca" type="button">
                                <i class="bi bi-lock-fill me-2"></i> Segurança e Senha
                            </button>
                            
                            {{-- Botão de Logout Seguro do Laravel --}}
                            <form action="{{ route('logout') }}" method="POST" id="formLogout" class="d-none">
                                @csrf
                            </form>
                            <button class="nav-link text-start py-3 text-danger mt-2 border-top" onclick="confirmarSaida()">
                                <i class="bi bi-box-arrow-right me-2"></i> Encerrar Sessão
                            </button>
                        </div>
                    </div>
                </div>

                {{-- CONTEÚDO DAS ABAS --}}
                <div class="col-md-8">
                    <div class="tab-content" id="v-pills-tabContent">
                        
                        {{-- ABA: MEUS DADOS --}}
                        <div class="tab-pane fade show active" id="tab-dados">
                            <div class="card card-custom p-4">
                                <h5 class="fw-bold mb-4 border-bottom pb-2">Informações da Conta</h5>
                                
                                <form action="{{ route('perfil.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">NOME COMPLETO <span class="text-danger">*</span></label>
                                        <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', Auth::user()->nome) }}" required maxlength="255">
                                        @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">E-MAIL</label>
                                        <input type="email" class="form-control bg-light" value="{{ Auth::user()->email }}" readonly>
                                        <small class="text-muted">O e-mail é a sua chave de acesso e não pode ser alterado.</small>
                                    </div>
                                    
                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                            <i class="bi bi-save me-2"></i>Salvar Alterações
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- ABA: SEGURANÇA E SENHA --}}
                        <div class="tab-pane fade" id="tab-seguranca">
                            <div class="card card-custom p-4">
                                <h5 class="fw-bold mb-4 border-bottom pb-2">Alterar Senha</h5>
                                
                                <form action="{{ route('perfil.password') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">SENHA ATUAL <span class="text-danger">*</span></label>
                                        <input type="password" name="senha_atual" class="form-control @error('senha_atual') is-invalid @enderror" placeholder="Digite sua senha atual" required>
                                        @error('senha_atual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <hr class="text-muted my-4">
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">NOVA SENHA <span class="text-danger">*</span></label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Mínimo 6 caracteres" required>
                                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">CONFIRMAR NOVA SENHA <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirmation" class="form-control" placeholder="Repita a nova senha" required>
                                    </div>
                                    
                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                            <i class="bi bi-shield-lock me-2"></i>Atualizar Senha
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function confirmarSaida() {
        Swal.fire({
            title: 'Sair do Sistema?',
            text: "Você precisará fazer login novamente para acessar seus planos.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, sair agora',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formLogout').submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Feedback Visual de Sucesso
        @if(session('success'))
            Swal.fire({
                title: 'Sucesso!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonColor: '#198754'
            });
        @endif

        // Mantém a aba de segurança aberta se der erro na troca de senha
        @if($errors->has('senha_atual') || $errors->has('password'))
            var triggerEl = document.querySelector('button[data-bs-target="#tab-seguranca"]');
            var tab = new bootstrap.Tab(triggerEl);
            tab.show();
        @endif
    });
</script>
@endpush