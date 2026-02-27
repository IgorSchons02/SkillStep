<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('titulo')</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    {{-- Inclui a navbar --}}
    @include('layout.navbar') 

    <main class="main-content flex-grow-1 pt-4">
        {{-- Conteúdo da página --}}
        @yield('conteudo')
    </main>

    {{-- Inclui o footer --}}
    @include('layout.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
    function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const icon = document.getElementById('toggle-icon');
    const mainContent = document.querySelector('.main-content');
    const siteFooter = document.querySelector('footer'); // <-- Seleciona o footer
    
    // Alterna a classe que define se está aberto ou fechado
    sidebar.classList.toggle('collapsed');
    
    // Ajusta a margem do conteúdo principal e do footer
    if (mainContent) {
        mainContent.classList.toggle('expanded');
    }
    if (siteFooter) { // <-- Adiciona a classe no footer
        siteFooter.classList.toggle('expanded');
    }
    
    // Troca o ícone de seta
    if (sidebar.classList.contains('collapsed')) {
        icon.classList.replace('bi-chevron-left', 'bi-chevron-right');
    } else {
        icon.classList.replace('bi-chevron-right', 'bi-chevron-left');
    }
}
</script>
</body>
</html>
