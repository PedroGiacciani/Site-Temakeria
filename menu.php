<style>
    img {
        border-radius: 50%;
        border: 3px solid #BB1F20;
        outline: 6px solid #1C1A1B;
    }
</style>

<a href="home.php"><img src="midias/logo-temakeria-bia.jpg" alt="Logo Temakeria da Bia"></a>
<h1>Temakeria da Bia</h1>

<nav id="menu">
    <i class="fa-solid fa-angle-down" onclick="toggleMenu()" id="icon"></i>
    <ul id="lista-menu">
        <li><a href="#" onclick="mostrarCardapio(); return false;">Nossos produtos</a></li>
        <li><a href="#">Seus pedidos</a></li>
        <?php if ($usuario ?? null): ?>
            <li><a href="#">Olá, <?= htmlspecialchars($usuario['nome']) ?></a></li>
            <li><a href="logout.php">Sair</a></li>
        <?php else: ?>
            <li><a href="login.php">Entrar</a></li>
            <li><a href="cadastro.php">Cadastrar</a></li>
        <?php endif; ?>
        <li><a href="quem-somos.php">Sobre nós</a></li>
    </ul>
</nav>

<script>
    // 1. Menu hamburguer (inalterado)
    function toggleMenu() {
        let lista = document.getElementById('lista-menu');
        let icone = document.getElementById('icon');

        lista.classList.toggle('aberto');

        if (lista.classList.contains('aberto')) {
            icone.classList.remove('fa-angle-down');
            icone.classList.add('fa-angle-up');
        } else {
            icone.classList.remove('fa-angle-up');
            icone.classList.add('fa-angle-down');
        }
    }

    // 2. Substitui o conteúdo do <main> pelo cardápio (universal)
    async function mostrarCardapio() {
        const mainContent = document.querySelector('main');
        if (!mainContent) {
            console.warn("Tag <main> não encontrada nesta página.");
            return;
        }

        // Verifica se o cardápio já está carregado (presença de #cardapio-ancora)
        if (mainContent.querySelector('#cardapio-ancora')) {
            // Já está no lugar, apenas rola suavemente
            document.getElementById('cardapio-ancora').scrollIntoView({ behavior: 'smooth' });
            return;
        }

        // Salva o conteúdo original? Não precisamos, pois vamos substituir de vez.
        // Mas se quiser manter um "voltar", seria outro recurso. Por enquanto, só substitui.

        try {
            const response = await fetch('cardapio.php');
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const html = await response.text();

            // Substitui todo o conteúdo do <main> pelo HTML do cardápio
            mainContent.innerHTML = html;

            // Garante que o cardápio fique visível (caso alguma classe de ocultação exista)
            const gaveta = document.getElementById('cardapio-ancora');
            if (gaveta) gaveta.classList.add('mostrar');

            // Rola suavemente para o cardápio
            mainContent.scrollIntoView({ behavior: 'smooth' });

            // Fecha o menu mobile se estiver aberto
            const listaMenu = document.getElementById('lista-menu');
            if (listaMenu && listaMenu.classList.contains('aberto')) {
                listaMenu.classList.remove('aberto');
                const icone = document.getElementById('icon');
                icone.classList.remove('fa-angle-up');
                icone.classList.add('fa-angle-down');
            }
        } catch (error) {
            console.error("Erro ao carregar cardápio:", error);
            mainContent.innerHTML = '<p style="color: red; text-align: center;">Erro ao carregar o cardápio. Tente novamente.</p>';
        }
    }

    // 3. Opcional: se quiser que links comuns (Sobre nós, Entrar) recarreguem a página normalmente, nada precisa ser feito.
</script>