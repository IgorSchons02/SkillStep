<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <button class="toggle-btn" onclick="toggleSidebar()">
                <i class="bi bi-chevron-left" id="toggle-icon"></i>
            </button>
            <span class="logo-text">SkillStep</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li class="{{ request()->routeIs('home*') ? 'active' : '' }}">
                {{-- Redireciona dinamicamente com base no tipo de usuário logado --}}
                <a href="{{ session('codigo_tipo') == 1 ? route('homeGestor') : route('homeColaborador') }}">
                    <i class="bi bi-house-door"></i>
                    <span>Home</span>
                </a>
            </li>
            {{-- Menu Visível apenas para Gestores --}}
            @if(session('codigo_tipo') == 1)
                <li>
                    <a href="{{ route('treinamentos.index') }}">
                        <i class="bi bi-journal-check"></i>
                        <span>Treinamentos</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('tarefas.index') }}">
                        <i class="bi bi-list-task"></i>
                        <span>Tarefas</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="bi bi-people"></i>
                        <span>Usuários</span>
                    </a>
                </li>
            @endif
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
            <button type="submit" class="logout-btn">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sair</span>
            </button>
        </form>
    </div>
</aside>