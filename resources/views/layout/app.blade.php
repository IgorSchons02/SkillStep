<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillStep - @yield('titulo', 'Início')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    @stack('css')
</head>

<body>

    @include('layout.navbar')

    <div class="wrapper">

        <main class="main-content">
            <div class="container-fluid pt-4 px-4">
                <!-- @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif -->

                @yield('content')
            </div>
        </main>

        @include('layout.footer')

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById("sidebar");
            const wrapper = document.querySelector(".wrapper");
            const toggleBtn = document.getElementById("sidebarToggle");
            const toggleIcon = document.getElementById("toggle-icon");

            if (toggleBtn && sidebar && wrapper) {
                toggleBtn.addEventListener("click", function () {
                    sidebar.classList.toggle("collapsed");
                    wrapper.classList.toggle("expanded");

                    if (toggleIcon) {
                        if (sidebar.classList.contains("collapsed")) {
                            toggleIcon.classList.replace("bi-chevron-left", "bi-chevron-right");
                        } else {
                            toggleIcon.classList.replace("bi-chevron-right", "bi-chevron-left");
                        }
                    }
                    localStorage.setItem("sidebar-collapsed", sidebar.classList.contains("collapsed"));
                });
            }

            if (localStorage.getItem("sidebar-collapsed") === "true" && sidebar) {
                sidebar.classList.add("collapsed");
                if (wrapper) wrapper.classList.add("expanded");
                if (toggleIcon) toggleIcon.classList.replace("bi-chevron-left", "bi-chevron-right");
            }

            // Fechar alertas automaticamente após 4s
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, 4000);
            });
        });
    </script>

    {{-- O Stack DEVE ser o último antes de fechar o body --}}
    @stack('js')
    {{-- Scripts de Notificação Globais --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

            // 1. Sucesso (Toast)
            @if(session('success'))
                Toast.fire({
                    icon: 'success',
                    title: "{{ session('success') }}"
                });
            @endif

            // 2. Erro de Sessão Único (Alert)
            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Ops...',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#e85d2f'
                });
            @endif

            // 3. Erros de Validação do Formulário (Lista de erros)
            @if($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Verifique os dados',
                    html: `<ul class="text-start mt-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                          </ul>`,
                    confirmButtonColor: '#e85d2f'
                });
            @endif
    });
    </script>
</body>

</html>
</body>

</html>