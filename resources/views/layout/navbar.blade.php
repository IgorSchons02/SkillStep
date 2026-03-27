<aside class="sidebar" id="sidebar">
    <div class="sidebar-header d-flex align-items-center justify-content-between">
        <span class="logo-text fw-bold fs-4">SkillStep</span>
        <button class="toggle-btn" id="sidebarToggle" type="button">
            <i class="bi bi-chevron-left" id="toggle-icon"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li class="{{ Route::is('home*') ? 'active' : '' }}">
                @php
                    $rotaHome = Auth::user()->isAdmin() ? 'homeAdmin' : (Auth::user()->isSupervisor() ? 'homeSupervisor' : 'homeAluno');
                @endphp
                <a href="{{ route($rotaHome) }}">
                    <i class="bi bi-house-door"></i>
                    <span>Home</span>
                </a>
            </li>

            <li class="{{ Route::is('planos.meu-plano') ? 'active' : '' }}">
                <a href="#">
                    <i class="bi bi-mortarboard"></i>
                    <span>Meu Plano</span>
                </a>
            </li>

            {{-- Menu Visível apenas para ADMIN --}}
            @if(Auth::user()->isAdmin())
                <hr class="mx-3 my-2 opacity-25 text-white">
                <li class="{{ Route::is('usuarios.*') ? 'active' : '' }}">
                    <a href="{{ route('usuarios.index') }}">
                        <i class="bi bi-people"></i>
                        <span>Usuários</span>
                    </a>
                </li>
                <li class="{{ Route::is('tarefas.*') ? 'active' : '' }}">
                    <a href="{{ route('tarefas.index') }}">
                        <i class="bi bi-list-task"></i>
                        <span>Tarefas</span>
                    </a>
                </li>
                <li class="{{ Route::is('categorias.*') ? 'active' : '' }}">
                    <a href="{{ route('categorias.index') }}">
                        <i class="bi bi-tag"></i>
                        <span>Categorias</span>
                    </a>
                </li>
                <li class="{{ Route::is('treinamentos.*') ? 'active' : '' }}">
                    <a href="{{ route('treinamentos.index') }}">
                        <i class="bi bi-journal-check"></i>
                        <span>Treinamentos</span>
                    </a>
                </li>
            @endif

            {{-- Menu Visível para SUPERVISOR --}}
            @if(Auth::user()->isSupervisor())
                <hr class="mx-3 my-2 opacity-25 text-white">
                <li>
                    <a href="#">
                        <i class="bi bi-person-video3"></i>
                        <span>Meus Alunos</span>
                    </a>
                </li>
            @endif

            <hr class="mx-3 my-2 opacity-25 text-white">
            <li>
                <a href="#">
                    <i class="bi bi-info-circle"></i>
                    <span>Sobre Nós</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn" title="Sair do sistema">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sair</span>
            </button>
        </form>
    </div>
</aside>